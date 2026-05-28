<?php

declare(strict_types=1);

namespace FastD\Http\Request;

use FastD\Http\Cookie;
use FastD\Http\Exception\ClientException;
use FastD\Http\Exception\NetworkException;
use FastD\Http\Exception\RequestException;
use FastD\Http\Response\Json;
use FastD\Http\Response\Text;
use FastD\Http\Stream\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * PSR-18 implemented
 */
class Client implements ClientInterface
{
    const USER_AGENT = 'PHP Curl/1.1 (+https://github.com/fastdlabs/http)';

    protected array $cookies = [];

    public function __construct(protected array $options = [
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => Client::USER_AGENT,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
    ])
    {
    }

    public function withCookie(Cookie $cookie): self
    {
        $new = clone $this;
        $new->cookies[$cookie->getName()] = $cookie;

        return $new;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function withOption(int $curlOpt, mixed $value): self
    {
        $new = clone $this;
        $new->options[$curlOpt] = $value;
        return $new;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        $request = $this->requestBefore($request, $options);

        return $this->sendRequest($request);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $options = $this->options + [
            CURLOPT_URL => (string) $request->getUri(),
            CURLOPT_POSTFIELDS => $request->getBody()->getContents(),
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_HTTPHEADER => $this->formatRequestHeaders($request),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $errorCode = curl_errno($ch);
        $errorMsg = curl_error($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 处理错误
        if ($errorCode !== 0) {
            throw match ($errorCode) {
                CURLE_OPERATION_TIMEDOUT => new NetworkException("Request timeout: " . $errorMsg, 408, null, $request),
                CURLE_COULDNT_RESOLVE_HOST, CURLE_COULDNT_CONNECT => new RequestException("Connection error: " . $errorMsg, 502, null, $request),
                CURLE_GOT_NOTHING => new RequestException("Gateway timeout: No response received", 504, null, $request),
                default => new ClientException("CURL error [{$errorCode}]: " . $errorMsg, 500),
            };
        }

        if ($response === false || $statusCode === 0) {
            throw new ClientException("No HTTP response code received", 500, null, $request);
        }

        // 拆解响应信息
        $responseParts = explode("\r\n\r\n", $response, 2);
        if (count($responseParts) !== 2) {
            throw new ClientException('Invalid response format', 500, null, $request);
        }

        list($responseHeaders, $responseBody) = $responseParts;
        $responseHeaders = $this->parseResponseHeaders($responseHeaders);

        if (isset($responseHeaders['content-encoding']) && in_array($responseHeaders['content-encoding'], ['gzip', 'deflate'])) {
            $decoded = zlib_decode($responseBody);
            if ($decoded !== false) {
                $responseBody = $decoded;
            }
        }

        // 确定响应类型
        $contentType = '';
        if (isset($responseHeaders['content-type'])) {
            $contentType = is_array($responseHeaders['content-type']) 
                ? $responseHeaders['content-type'][0] 
                : $responseHeaders['content-type'];
        }
        
        $isJson = str_contains($contentType, 'application/json');
        $response = $isJson ? Json::class : Text::class;

        // 创建响应对象
        if ($isJson) {
            $response = new $response($statusCode, json_decode($responseBody, true) ?? [], $responseHeaders);
        } else {
            $response = new $response($statusCode, $responseBody ?? '', $responseHeaders);
        }

        // 设置可能存在的 cookie 信息
        foreach ($this->cookies as $cookie) {
            $response = $response->withCookie($cookie);
        }

        return $response;
    }

    public function requestBefore(RequestInterface $request, array $options = []): RequestInterface
    {
        // 处理头部
        if (!empty($options['headers'])) {
            foreach ($options['headers'] as $header => $value) {
                $request = $request->withHeader($header, $value);
            }
        }

        // 处理查询参数
        if (!empty($options['query'])) {
            $uri = $request->getUri()->withQuery(http_build_query($options['query']));
            $request = $request->withUri($uri);
        }

        // 处理请求体
        if (!empty($options['body'])) {
            $request->getBody()->close();
            $body = $this->buildRequestBody($options['body'], $request);
            $body->rewind();
            $request = $request->withBody($body);
        }

        return $request;
    }

    private function buildRequestBody(mixed $rawData, RequestInterface $request): StreamInterface
    {
        $contentTypeLine = $request->hasHeader('content-type') ? $request->getHeaderLine('content-type') : 'application/x-www-form-urlencoded';
        $contentType = trim(explode(';', $contentTypeLine)[0]);

        $formattedData = match (strtolower($contentType)) {
            // 表单格式：application/x-www-form-urlencoded
            'application/x-www-form-urlencoded' => is_array($rawData) ? http_build_query($rawData) : $rawData,
            // JSON 格式：application/json
            'application/json' => json_encode($rawData, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION),
            // 表单上传格式：multipart/form-data（仅支持数组，返回原始数组序列化流，cURL 自动处理）
            'multipart/form-data' => serialize($rawData),
            // 纯文本格式：text/plain
            'text/plain' => (string)$rawData,
            // 不支持的 Content-Type
            default => throw new RuntimeException("Cannot support Content-Type：{$contentType}"),
        };

        return Stream::create($formattedData);
    }

    private function formatRequestHeaders(RequestInterface $request): array
    {
        $headers = $request->getHeaders();
        $cookieStr = '';
        foreach ($this->cookies as $cookie) {
            $encodedKey = urlencode((string)$cookie->getName());
            $encodedValue = urlencode((string)$cookie->getValue());
            $cookieStr .= "{$encodedKey}={$encodedValue}; ";
        }
        $headers['Cookie'] = rtrim($cookieStr, '; ');

        $formatted = [];
        foreach ($headers as $name => $values) {
            foreach ((array)$values as $value) {
                $formatted[] = "{$name}: {$value}";
            }
        }

        return $formatted;
    }

    /**
     * 返回 key 名全小写的 headers 键对值
     *
     * @param string $headers
     * @return array
     */
    private function parseResponseHeaders(string $headers): array
    {
        $responseHeaders = preg_split('/\r\n/', $headers, -1, PREG_SPLIT_NO_EMPTY);
        array_shift($responseHeaders);

        $parsedHeaders = [];
        foreach ($responseHeaders as $line) {
            if (($pos = strpos($line, ':')) === false) {
                continue;
            }

            [$name, $value] = explode(':', $line, 2);

            $name = strtolower($name);
            $value = trim($value);

            if ('set-cookie' === $name) {
                $cookie = $this->parseResponseCookie($value);
                $this->cookies[$cookie->getName()] = $cookie;
            } else {
                $parsedHeaders[$name] = str_contains($value, ';') ? explode(';', $value) : $value;
            }
        }

        return $parsedHeaders;
    }

    private function parseResponseCookie(string $cookieLine): Cookie
    {
        $cookie = [
            'value' => '', 'expires' => null, 'max-age' => null,
            'domain' => '', 'path' => '/', 'secure' => false, 'httponly' => false, 'samesite' => 'Lax'
        ];

        // 分离 name=value 和属性部分
        [$nameValue, $attributes] = array_pad(explode(';', $cookieLine, 2), 2, '');
        // 解析 name=value
        if (($equalPos = strpos($nameValue, '=')) !== false) {
            [$name, $value] = explode('=', $nameValue, 2);
            $cookie['name'] = trim($name);
            $cookie['value'] = urldecode(trim($value));
        } else {
            $cookie['name'] = $nameValue;
        }

        $attrMap = [
            'expires' => 'expires',
            'max-age' => 'max-age',
            'domain' => 'domain',
            'path' => 'path',
            'samesite' => 'samesite'
        ];

        // 解析属性
        foreach (explode(';', $attributes) as $attr) {
            $attr = trim($attr);
            if (empty($attr)) continue;

            if (($attrPos = strpos($attr, '=')) !== false) {
                [$attrName, $attrValue] = explode('=', $attr, 2);
                $attrName = strtolower($attrName);

                // 仅处理已知属性
                if (isset($attrMap[$attrName])) {
                    $cookie[$attrName] = $attrName === 'max-age' ? (int)$attrValue : $attrValue;
                }
            } else {
                $attrName = strtolower($attr);
                if ($attrName === 'secure') $cookie['secure'] = true;
                elseif ($attrName === 'httponly') $cookie['httponly'] = true;
            }
        }

        // 处理 expires，转换为 Unix 时间戳（相对于当前时间的秒数）
        $expire = -1;
        if (!empty($cookie['expires'])) {
            $timestamp = strtotime($cookie['expires']);
            if ($timestamp !== false) {
                $expire = $timestamp - time();
            }
        } elseif (!empty($cookie['max-age'])) {
            $expire = (int)$cookie['max-age'];
        }

        return new Cookie(
            $cookie['name'],
            $cookie['value'],
            $expire,
            $cookie['path'],
            $cookie['domain'],
            $cookie['secure'],
            $cookie['httponly'],
            $cookie['samesite']
        );
    }
}