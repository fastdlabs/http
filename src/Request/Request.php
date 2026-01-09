<?php

declare(strict_types=1);

namespace FastD\Http\Request;

use FastD\Http\Message;
use FastD\Http\Response\Text;
use FastD\Http\Stream\Stream;
use FastD\Http\Uri;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class Request extends Message implements RequestInterface
{
    protected string $method = 'GET';

    protected UriInterface $uri;

    private array $validMethods = [
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
    ];

    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        ?StreamInterface $stream = new Stream(),
        string $version = '1.1'
    )
    {
        parent::__construct($stream, $version);
        
        $method = strtoupper($method);
        if (!in_array($method, $this->validMethods, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported HTTP method "%s" provided', $method));
        }

        $this->method = $method;
        $this->uri = new Uri($uri);

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
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget(): string
    {
        return $this->uri->getPath();
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     * @param string $requestTarget
     * @return Request
     */
    public function withRequestTarget(string $requestTarget): Request
    {
        $new = clone $this;
        $new->uri = $this->uri->withPath($requestTarget);

        return $new;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * changed request method.
     *
     * @param string $method Case-sensitive method.
     * @return Request
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod(string $method): Request
    {
        $method = strtoupper($method);

        if (!in_array($method, $this->validMethods, true)) {
            throw new InvalidArgumentException(sprintf('Unsupported HTTP method "%s" provided', $method));
        }

        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return Request
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;

        // 处理 Host header 逻辑（根据 preserveHost 参数和 URI 是否包含主机）
        if (!$preserveHost && '' !== ($host = $uri->getHost())) {
            if (null === ($port = $uri->getPort())) {
                $host .= ':' . $port;
            }
            $new->headers['Host'] = $host;
        }

        return $new;
    }
}
