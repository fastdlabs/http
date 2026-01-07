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
use Psr\Http\Message\ResponseInterface;
use Stringable;

class Text extends Message implements ResponseInterface, StatusCode, Stringable
{
    /**
     * Http response status code reason phrase.
     *
     * @var string
     */
    protected string $reasonPhrase;

    /**
     * @var DateTime
     */
    protected DateTime $maxAge;

    /**
     * @var Cookie[]
     */
    protected array $cookies = [];

    /**
     * @var int
     */
    protected ?int $fileDescriptor = null;

    /**
     * Response constructor.
     *
     * @param string $content
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct(string $content = '', protected int $statusCode = StatusCode::HTTP_OK, array $headers = [])
    {
        parent::__construct(new Stream('php://memory', 'wb+'));
        $this->withStatus($statusCode);
        $this->withHeaders($headers);
        $this->withContents($content);
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return void
     */
    public function send(): void
    {
        if (!headers_sent()) {
            header(
                sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $this->getStatusCode(), ($this->isOk() ? '' : $this->getReasonPhrase())),
                true,
                $this->getStatusCode()
            );

            foreach ($this->headers as $name => $value) {
                $name = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name)));
                header(sprintf('%s: %s', $name, implode(',', $value)), false, $this->statusCode);
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

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return Text
     */
    public function withCookie(string $name, string $value = '', int $expire = -1, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): Text
    {
        $this->cookies[$name] = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);

        return $this;
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @param $fd
     * @return Text
     */
    public function withFileDescriptor(int $fd): Text
    {
        $this->fileDescriptor = $fd;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileDescriptor(): ?int
    {
        return $this->fileDescriptor;
    }

    public function withHeaders(array $headers): Text
    {
        foreach ($headers as $key => $header) {
            if (is_array($header)) {
                foreach ($header as $item) {
                    $this->withAddedHeader($key, $item);
                }
            } else {
                $this->withHeader($key, $header);
            }
        }

        return $this;
    }

    public function withContents(string $content): Text
    {
        $this->getBody()->write($content);

        return $this;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        $this->getBody()->rewind();

        return $this->getBody()->getContents();
    }

    public function withContentType(string $contentType): Text
    {
        $this->withoutHeader('Content-Type');
        $this->withHeader('Content-Type', $contentType);

        return $this;
    }

    public function getContentType(): string
    {
        return $this->getHeaderLine('Content-Type');
    }

    /**
     * @param string $cacheControl
     * @return Text
     */
    public function withCacheControl(string $cacheControl): Text
    {
        $this->withoutHeader('Cache-Control');
        $this->withHeader('Cache-Control', $cacheControl);

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheControl(): string
    {
        return $this->getHeaderLine('Cache-Control');
    }

    /**
     * Sets the ETag value.
     *
     * @param string $eTag The ETag unique identifier or null to remove the header
     * @param bool $weak Whether you want a weak ETag or not
     *
     * @return Text
     */
    public function withETag(string $eTag = '', bool $weak = false): Text
    {
        $this->withoutHeader('ETag');
        $this->withHeader('ETag', (true === $weak ? 'W/' : '') . $eTag);

        return $this;
    }

    /**
     * Returns the literal value of the ETag HTTP header.
     *
     * @return string The ETag HTTP header or null if it does not exist
     */
    public function getETag(): string
    {
        return $this->getHeaderLine('ETag');
    }

    /**
     * @param DateTime $date
     * @return $this
     * @throws Exception
     */
    public function withExpires(DateTime $date): Text
    {
        $timezone = new DateTimeZone("GMT");

        $this->withoutHeader('Expires');
        $date->setTimezone($timezone);
        $this->withHeader('Expires', $date->format('D, d M Y H:i:s') . ' GMT');

        $maxAge = max(0, $date->getTimestamp() - time());
        $this->withMaxAge($maxAge);

        return $this;
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     *
     * @return DateTime A DateTime instance or null if the header does not exist
     */
    public function getExpires(): DateTime
    {
        try {
            return new DateTime($this->getHeaderLine('Expires'));
        } catch (Exception $e) {
            // according to RFC 2616 invalid date formats (e.g. "0" and "-1") must be treated as in the past
            return DateTime::createFromFormat(DATE_RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
        }
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @param int $value Number of seconds
     *
     * @return Text
     */
    public function withMaxAge(int $value): Text
    {
        $this->maxAge = new DateTime("+$value seconds");

        $this->withAddedHeader('Cache-Control', 'max-age=' . $value);

        return $this;
    }

    /**
     * Returns the number of seconds after the time specified in the response's Date
     * header when the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @return int Number of seconds
     */
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

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @param int $value Number of seconds
     *
     * @return Text
     */
    public function withSharedMaxAge(int $value): Text
    {
        $this->withCacheControl('public');
        $this->withAddedHeader('Cache-Control', 's-maxage=' . max(0, $value));

        return $this;
    }

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param DateTime|null $date A \DateTime instance or null to remove the header
     * @return $this
     */
    public function withLastModified(DateTime $date): Text
    {
        $this->withoutHeader('Last-Modified');
        $this->withHeader('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * @return string
     */
    public function getLastModified(): string
    {
        return $this->getHeaderLine('Last-Modified');
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @return Text
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     */
    public function withNotModified(): Text
    {
        $this->withStatus(static::HTTP_NOT_MODIFIED);
        $this->getBody()->rewind();
        $this->getBody()->write('');

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
            $this->withoutHeader($header);
        }

        return $this;
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
        if ($code < 100 || $code >= 600) {
            throw new InvalidArgumentException(sprintf('Invalid status code "%s"; must be an integer between 100 and 599, inclusive', $code));
        }

        $this->statusCode = $code;

        $this->reasonPhrase = null === $reasonPhrase ? StatusCode::STATUS_TEXT[$this->statusCode] ?? 'Unknown phrase' : $reasonPhrase;

        return $this;
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

    /**
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * Is response invalid?
     *
     * @return bool
     */
    public function isInvalidStatusCode(): bool
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response successful?
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Was there a server side error?
     *
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @return bool
     */
    public function isNotFound(): bool
    {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect?
     *
     * @return bool
     */
    public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     */
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
