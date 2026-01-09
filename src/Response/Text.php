<?php

declare(strict_types=1);

namespace FastD\Http\Response;

use DateTime;
use DateTimeZone;
use Exception;
use FastD\Http\Cookie;
use FastD\Http\Message;
use FastD\Http\Stream\Stream;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Stringable;

class Text extends Message implements ResponseInterface, StatusCode, Stringable
{
    protected string $reasonPhrase;

    protected array $cookies = [];

    public function __construct(protected int $statusCode = StatusCode::HTTP_OK, string $content = '', array $headers = [], string $protocolVersion = '1.1')
    {
        // 首先验证状态码
        $this->assertStatusCodeRange($this->statusCode);;

        // 设置内容
        parent::__construct(Stream::create($content), $protocolVersion);

        // 初始化状态码，响应头
        $this->statusCode = $statusCode;
        $this->reasonPhrase = StatusCode::PHRASES[$this->statusCode] ?? 'Unknown phrase';

        foreach ($headers as $header => $value) {
            $header = strtolower((string) $header);
            if (isset($this->headers[$header])) {
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headers[$header] = $value;
            }
        }
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code The 3-digit integer result code to set.
     * @param string|null $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return Text
     * @throws InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus(int $code, ?string $reasonPhrase = null): ResponseInterface
    {
        $this->assertStatusCodeRange($code);

        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = null === $reasonPhrase ? StatusCode::PHRASES[$new->statusCode] ?? 'Unknown phrase' : $reasonPhrase;

        return $new;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    // 以下自定义 with 方法不需要克隆，因为原则上使用 Message::withHeader 方法，而该方法中已经存在 clone 行为
    public function withContentType(string $contentType): MessageInterface
    {
        return $this->withHeader('Content-Type', $contentType);
    }

    public function getContentType(): string
    {
        return $this->getHeaderLine('Content-Type');
    }

    public function withCacheControl(string $cacheControl): MessageInterface
    {
        return $this->withHeader('Cache-Control', $cacheControl);
    }

    public function getCacheControl(): string
    {
        return $this->getHeaderLine('Cache-Control');
    }

    public function withETag(string $eTag = '', bool $weak = false): MessageInterface
    {
        return $this->withHeader('ETag', (true === $weak ? 'W/' : '') . $eTag);
    }

    public function getETag(): string
    {
        return $this->getHeaderLine('ETag');
    }

    public function withExpires(DateTime $date): MessageInterface
    {
        $date->setTimezone(new DateTimeZone("GMT"));
        $maxAge = max(0, $date->getTimestamp() - time());

        return $this
            ->withHeader('Expires', $date->format('D, d M Y H:i:s') . ' GMT')
            ->withAddedHeader('Cache-Control', 'max-age=' . $maxAge);
    }

    public function getExpires(): DateTime
    {
        try {
            return new DateTime($this->getHeaderLine('Expires'));
        } catch (Exception $e) {
            // according to RFC 2616 invalid date formats (e.g. "0" and "-1") must be treated as in the past
            return DateTime::createFromFormat(DATE_RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
        }
    }

    public function withMaxAge(int $value): MessageInterface
    {
        return $this->withAddedHeader('Cache-Control', 'max-age=' . $value);
    }

    public function getMaxAge(): int
    {
        $cacheControl = $this->getHeaderLine('Cache-Control');

        if (preg_match('/max-age=(\d+)/', $cacheControl, $matches)) {
            return (int)$matches[1];
        }

        // 如果没有max-age，返回基于Expires的值
        $expires = $this->getExpires();
        $now = new DateTime();

        return max(0, $expires->getTimestamp() - $now->getTimestamp());
    }

    public function withSharedMaxAge(int $value): MessageInterface
    {
        return $this
            ->withCacheControl('public')
            ->withAddedHeader('Cache-Control', 's-maxage=' . max(0, $value));
    }

    public function withLastModified(DateTime $date): MessageInterface
    {
        return $this->withHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');
    }

    public function getLastModified(): string
    {
        return $this->getHeaderLine('Last-Modified');
    }

    public function withNotModified(): MessageInterface
    {
        $new = $this->withStatus(static::HTTP_NOT_MODIFIED);

        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach ([
                     'Allow',
                     'Content-Encoding',
                     'Content-Language',
                     'Content-Length',
                     'Content-MD5',
                     'Content-Type',
                     'Last-Modified',
                 ] as $header) {
            $new = $new->withoutHeader($header);
        }

        // Clear and close the body content by replacing with an empty stream. detach and close resource
        $new->getBody()->close();
        return $new->withBody(new Stream('php://memory', 'wb+'));
    }

    public function withCookie(Cookie $cookie): MessageInterface
    {
        $new = clone $this;
        $new->cookies[$cookie->getName()] = $cookie;
        return $new;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getContents(): string
    {
        $this->getBody()->rewind();

        return $this->getBody()->getContents();
    }

    public function send(): void
    {
        if (!headers_sent()) {
            header(sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(), ($this->statusCode === 200 ? '' : $this->getReasonPhrase())), true, $this->getStatusCode());

            foreach ($this->headers as $header => $value) {
                header(sprintf('%s: %s', $header, implode(',', $value)), false, $this->statusCode);
            }

            foreach ($this->cookies as $cookie) {
                header(sprintf('Set-Cookie: %s', (string)$cookie), false, $this->getStatusCode());
            }
        }

        echo $this->getBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    private function assertStatusCodeRange(int $statusCode): void
    {
        if ($statusCode < 100 || $statusCode >= 600) {
            throw new \InvalidArgumentException('Status code must be an integer value between 1xx and 5xx.');
        }
    }

    public function __toString(): string
    {
        $headerLine = '';
        foreach ($this->headers as $name => $value) {
            $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
            $headerLine .= $name . ': ' . $this->getHeaderLine($name) . "\r\n";
        }

        foreach ($this->cookies as $cookie) {
            $headerLine .= sprintf('Set-Cookie: %s', (string)$cookie) . "\r\n";
        }

        return sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(), $this->getReasonPhrase()) . "\r\n" .
            $headerLine . "\r\n" .
            $this->getContents();
    }
}
