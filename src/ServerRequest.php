<?php
declare(strict_types=1);
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;


use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class ServerRequest
 * @package FastD\Http
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    public array $attributes = [];

    /**
     * @var array
     */
    public array $cookieParams = [];

    /**
     * @var array
     */
    public array $queryParams = [];

    /**
     * @var array
     */
    public array $bodyParams = [];

    /**
     * @var array
     */
    public array $serverParams = [];

    /**
     * @var UploadedFile[]
     */
    public array $uploadFile = [];

    /**
     * ServerRequest constructor.
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param StreamInterface|null $body
     * @param array $serverParams
     */
    public function __construct(
        string          $method,
        string          $uri,
        array           $headers = [],
        StreamInterface $body = null,
        array           $serverParams = []
    )
    {
        parent::__construct($method, $uri, $headers, $body);

        $this
            ->withQueryParams($this->uri->getQuery())
            ->withServerParams($serverParams)
            ->withParsedBody($_POST)
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles($_FILES);

        if (in_array(strtoupper($method), ['PUT', 'DELETE', 'PATCH', 'OPTIONS'])) {
            parse_str((string)$body, $data);
            if (empty($data)) {
                $data = json_decode((string)$body);
            }

            $this->withParsedBody($data);
        }
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * @param array $server
     * @return ServerRequest
     */
    public function withServerParams(array $server): ServerRequest
    {
        if (empty($this->header)) {
            array_walk($server, function ($value, $key) {
                if (0 === strpos($key, 'HTTP_')) {
                    $this->withAddedHeader(str_replace('HTTP_', '', $key), $value);
                }
            });
        }

        $this->serverParams = $server;

        return $this;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * @param $key
     * @param $default
     * @return bool|mixed
     */
    public function getCookie(string $key, bool $default = null): ?Cookie
    {
        return $this->cookieParams[$key] ?? $default;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return ServerRequest
     */
    public function withCookieParams(array $cookies): ServerRequest
    {
        foreach ($cookies as $name => $value) {
            $this->cookieParams[$name] = $value;
        }

        return $this;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function getParam(string $key, ?string $default = null)
    {
        if (isset($this->queryParams[$key])) {
            return $this->queryParams[$key];
        }

        if (isset($this->bodyParams[$key])) {
            return $this->bodyParams[$key];
        }

        return $default;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     * @return ServerRequest
     */
    public function withQueryParams(array $query): ServerRequest
    {
        foreach ($query as $name => $value) {
            $this->queryParams[$name] = $value;
        }

        return $this;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return UploadedFile[] An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadFile;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param UploadedFile[] $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return ServerRequest
     * @throws InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequest
    {
        $this->uploadFile = static::normalizer($uploadedFiles);

        return $this;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return array The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody(): array
    {
        return $this->bodyParams;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     * @return ServerRequest
     * @throws InvalidArgumentException if an unsupported argument type is
     *                                provided.
     */
    public function withParsedBody($data): ServerRequest
    {
        $this->bodyParams = $data;

        return $this;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     * @see getAttributes()
     */
    public function getAttribute(string $name, $default = null)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     * @see getAttributes()
     */
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param string $name The attribute name.
     * @return ServerRequest
     * @see getAttributes()
     */
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        if (!isset($this->attributes[$name])) {
            return $this;
        }

        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * @return string
     */
    public function getClientIP(): string
    {
        $unknown = 'unknown';
        $ip = 'unknown';
        if (
            isset($this->serverParams['HTTP_X_FORWARDED_FOR'])
            && $this->serverParams['HTTP_X_FORWARDED_FOR']
            && strcasecmp($this->serverParams['HTTP_X_FORWARDED_FOR'], $unknown)
        ) {
            $ip = $this->serverParams['HTTP_X_FORWARDED_FOR'];
        } else if (
            isset($this->serverParams['REMOTE_ADDR'])
            && $this->serverParams['REMOTE_ADDR']
            && strcasecmp($this->serverParams['REMOTE_ADDR'], $unknown)
        ) {
            $ip = $this->serverParams['REMOTE_ADDR'];
        }

        if (false !== strpos($ip, ',')) {
            $ip = explode(',', $ip);
            reset($ip);
        }

        return $ip;
    }

    /**
     * @param $files
     * @return array
     */
    public static function normalizer(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (!is_array($value['name'])) {
                $normalized[$key] = UploadedFile::normalizer($value);
            } elseif (is_array($value['name'])) {
                $array = [];
                foreach ($value['name'] as $index => $item) {
                    if (empty($item)) {
                        continue;
                    }
                    $array[] = UploadedFile::normalizer([
                        'name' => $value['name'][$index],
                        'type' => $value['type'][$index],
                        'tmp_name' => $value['tmp_name'][$index],
                        'error' => $value['error'][$index],
                        'size' => $value['size'][$index],
                    ]);
                }
                $normalized[$key] = $array;
                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * @param array $serverParams
     * @return string
     */
    public static function createUriFromGlobal(array $serverParams): string
    {
        $uri = 'http://';
        if (isset($serverParams['REQUEST_SCHEME'])) {
            $uri = strtolower($serverParams['REQUEST_SCHEME']) . '://';
        } else {
            if (isset($serverParams['HTTPS']) && 'on' === $serverParams['HTTPS']) {
                $uri = 'https://';
            }
        }
        if (isset($serverParams['SERVER_NAME'])) {
            $uri .= $serverParams['SERVER_NAME'];
        } elseif (isset($serverParams['HTTP_HOST'])) {
            $uri .= $serverParams['HTTP_HOST'];
        }
        if (isset($serverParams['SERVER_PORT']) && !empty($serverParams['SERVER_PORT'])) {
            if (!in_array($serverParams['SERVER_PORT'], [80, 443])) {
                $uri .= ':' . $serverParams['SERVER_PORT'];
            }
        }
        if (isset($serverParams['REQUEST_URI'])) {
            $requestUriParts = explode('?', $serverParams['REQUEST_URI']);
            $uri .= $requestUriParts[0];
            unset($requestUriParts);
        }
        if (isset($serverParams['QUERY_STRING']) && !empty($serverParams['QUERY_STRING'])) {
            $uri .= '?' . $serverParams['QUERY_STRING'];
        }

        return $uri;
    }

    /**
     * Create a new server request from PHP globals.
     *
     * @return ServerRequest
     */
    public static function createServerRequestFromGlobals(): ServerRequest
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        foreach ($headers as $name => $value) {
            unset($headers[$name]);
            $name = str_replace('-', '_', $name);
            $headers[$name] = $value;
        }

        return new static($method, static::createUriFromGlobal($_SERVER), $headers, new PhpInputStream(), $_SERVER);
    }
}
