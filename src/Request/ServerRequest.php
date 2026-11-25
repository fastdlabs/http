<?php

declare(strict_types=1);

namespace FastD\Http\Request;

use FastD\Http\Cookie;
use FastD\Http\Stream\Stream;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    protected array $attributes = [];

    protected array $cookieParams = [];

    protected array $queryParams = [];

    protected null|array|object $parsedBody = [];

    protected array $serverParams = [];

    protected array $uploadFile = [];

    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        ?StreamInterface $body = new Stream(),
        string $version = '1.1',
        array $serverParams = []
    )
    {
        $this->serverParams = $serverParams;

        parent::__construct($method, $uri, $headers, $body, $version);
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
     * @param string $key
     * @return Cookie|null
     */
    public function getCookie(string $key): ?Cookie
    {
        $value = $this->cookieParams[$key] ?? null;
        if ($value === null) {
            return null;
        }
        // 如果$value已经是Cookie对象，直接返回；否则创建新的Cookie对象
        if ($value instanceof Cookie) {
            return $value;
        }
        return new Cookie($key, (string)$value);
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
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
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
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
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
        $new = clone $this;
        $new->uploadFile = $uploadedFiles;

        return $new;
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
    public function getParsedBody(): array|object|null
    {
        return $this->parsedBody;
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
    public function withParsedBody(mixed $data): ServerRequest
    {
        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
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
    public function getAttribute(string $name, mixed $default = null): mixed
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
    public function withAttribute(string $name, mixed $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
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
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }

    public static function normalizeFiles(array $files): array
    {
        $result = [];
        foreach ($files as $key => $value) {
            if (is_string($value['name'])) {
                // Single file
                $result[$key] = $value instanceof UploadedFileInterface ? $value : new UploadedFile(
                    $value['name'],
                    $value['type'],
                    $value['tmp_name'],
                    $value['size'],
                    $value['error']
                );
            } elseif (is_array($value['name'])) {
                // Multiple files
                $fileList = [];
                $count = count($value['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (isset($value['name'][$i]) && $value['name'][$i]) {
                        $fileList[] = new UploadedFile(
                            $value['name'][$i],
                            $value['type'][$i],
                            $value['tmp_name'][$i],
                            $value['size'][$i],
                            $value['error'][$i]
                        );
                    }
                }
                $result[$key] = $fileList;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }
        return $result;
    }

    public static function createUriFromBoth(array $serverParams): string
    {
        $uri = 'http://';
        if (isset($serverParams['REQUEST_SCHEME'])) {
            $uri = strtolower($serverParams['REQUEST_SCHEME']) . '://';
        } else {
            if (isset($serverParams['HTTPS']) && 'on' === $serverParams['HTTPS']) {
                $uri = 'https://';
            }
        }
        if (isset($serverParams['HTTP_HOST'])) {
            $uri .= $serverParams['HTTP_HOST'];
        } elseif (isset($serverParams['SERVER_NAME'])) {
            $uri .= $serverParams['SERVER_NAME'];
            if (!empty($serverParams['SERVER_PORT'])) {
                if (!in_array($serverParams['SERVER_PORT'], [80, 443])) {
                    $uri .= ':' . $serverParams['SERVER_PORT'];
                }
            }
        } elseif (isset($serverParams['HTTP_HOST'])) {
            $uri .= $serverParams['HTTP_HOST'];
            // HTTP_HOST 可能已经包含端口，不需要再次添加
        }
        if (isset($serverParams['REQUEST_URI'])) {
            $requestUriParts = explode('?', $serverParams['REQUEST_URI']);
            $uri .= $requestUriParts[0];
            unset($requestUriParts);
        }
        if (!empty($serverParams['QUERY_STRING'])) {
            $uri .= '?' . $serverParams['QUERY_STRING'];
        }

        return $uri;
    }

    public static function fromGlobals(?string $rawBody = null): ServerRequest
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = self::globalHeaders();
        $version = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';
        $rawBody ??= self::readRawBody();
        $body = Stream::create($rawBody);
        $parsed = static::parseBody($method, $rawBody, $_POST, $headers);

        $serverRequest = new ServerRequest($method, static::createUriFromBoth($_SERVER), $headers, $body, $version, $_SERVER);

        return $serverRequest
            ->withQueryParams($_GET)
            ->withParsedBody($parsed)
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles(static::normalizeFiles($_FILES));
    }

    /**
     * Parse a raw request body into the PSR-7 parsed body value.
     *
     * JSON media types are decoded as associative arrays so callers receive a
     * PHP array for both JSON objects and JSON lists. Form-encoded bodies on
     * methods PHP does not populate in $_POST are parsed from the raw body.
     *
     * @param string $method HTTP request method.
     * @param string $rawBody Raw request body.
     * @param array $post Values already parsed by PHP, normally $_POST.
     * @param array $headers Request headers with case-insensitive names.
     * @return array|object|null
     */
    protected static function parseBody(string $method, string $rawBody, array $post, array $headers): array|object|null
    {
        if ($rawBody !== '' && self::isJsonContentType(self::headerValue($headers, 'content-type'))) {
            $decoded = json_decode($rawBody, true);

            return is_array($decoded) ? $decoded : [];
        }

        if ($rawBody !== '' && in_array(strtoupper($method), ['PUT', 'DELETE', 'PATCH', 'OPTIONS'], true)) {
            parse_str($rawBody, $parsed);

            return $parsed === [] ? $post : $parsed;
        }

        return $post;
    }

    /**
     * Build request headers from the CGI environment.
     *
     * @return array
     */
    private static function globalHeaders(): array
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        if (self::headerValue($headers, 'content-type') === '' && isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }

    /**
     * Read the request body from php://input.
     *
     * @return string
     */
    private static function readRawBody(): string
    {
        $rawBody = file_get_contents('php://input');

        return $rawBody === false ? '' : $rawBody;
    }

    /**
     * Read one header value case-insensitively.
     *
     * @param array $headers Request headers.
     * @param string $name Header name.
     * @return string
     */
    private static function headerValue(array $headers, string $name): string
    {
        foreach ($headers as $key => $value) {
            if (strcasecmp((string)$key, $name) !== 0) {
                continue;
            }

            return is_array($value) ? implode(', ', array_map('strval', $value)) : trim((string)$value);
        }

        return '';
    }

    /**
     * Whether a Content-Type value denotes JSON.
     *
     * @param string $contentType Content-Type header value.
     * @return bool
     */
    private static function isJsonContentType(string $contentType): bool
    {
        $mediaType = strtolower(trim(explode(';', $contentType, 2)[0]));

        return $mediaType === 'application/json' || str_ends_with($mediaType, '+json');
    }
}
