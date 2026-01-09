<?php

declare(strict_types=1);

namespace FastD\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Stringable;

class Uri implements UriInterface, Stringable
{
    const CHAR_SUB_DELIMITERS = '!\$&\'\(\)\*\+,;=';

    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    protected array $allowedSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    protected string $scheme = '';

    protected string $userInfo = '';

    protected string $host = '';

    protected ?int $port = null;

    protected string $path = '';

    protected string $query = '';

    protected array $queryParams = [];

    protected string $fragment = '';

    protected array $fragmentParams = [];

    protected string $uriString = '';

    public function __construct(string $uri = '')
    {
        if ('' !== $uri) $this->parseUri($uri);
    }

    /**
     * Operations to perform on clone.
     *
     * Since cloning usually is for purposes of mutation, we reset the
     * $uriString property so it will be re-calculated.
     */
    public function __clone()
    {
        $this->uriString = '';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ('' !== $this->uriString) {
            return $this->uriString;
        }

        $this->uriString = '';

        if ($this->scheme !== '') {
            $this->uriString .= sprintf('%s://', $this->scheme);
        }

        $this->uriString .= $this->getAuthority();

        $path = $this->path;
        if ($path !== '' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        $this->uriString .= $path;

        if ($this->query !== '') {
            $this->uriString .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $this->uriString .= '#' . $this->fragment;
        }

        return $this->uriString;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        if (empty($this->host)) {
            return '';
        }

        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->isNonStandardPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getFragmentParams(): array
    {
        return $this->fragmentParams;
    }

    public function withScheme(string $scheme): UriInterface
    {
        if ($scheme === $this->scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $new->filterScheme($scheme);

        return $new;
    }

    public function withUserInfo(string $user, ?string $password = ''): UriInterface
    {
        $info = $user;
        if ($password) {
            $info .= ':' . $password;
        }

        if ($info === $this->userInfo) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $info;

        return $new;
    }

    public function withHost(string $host): UriInterface
    {
        if ($host === $this->host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    public function withPort(?int $port): UriInterface
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%d" specified; must be a valid TCP/UDP port',
                $port
            ));
        }

        if ($port === $this->port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    public function withPath(string $path): UriInterface
    {
        if (str_contains($path, '?')) {
            throw new InvalidArgumentException('Invalid path provided; must not contain a query string');
        }

        if (str_contains($path, '#')) {
            throw new InvalidArgumentException('Invalid path provided; must not contain a URI fragment');
        }

        if ($path === $this->path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $new->filterPath($path);

        return $new;
    }

    public function withQuery(string $query): UriInterface
    {
        if (str_contains($query, '#')) {
            throw new InvalidArgumentException('Query string must not include a URI fragment');
        }

        if ($query === $this->query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $new->filterQuery($query);
        $new->queryParams = [];
        parse_str($new->query, $new->queryParams);

        return $new;
    }

    public function withFragment(string $fragment): UriInterface
    {
        if ($fragment === $this->fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $new->filterFragment($fragment);

        return $new;
    }

    /**
     * Parse a URI into its parts, and set the properties
     *
     * @param string $uri
     */
    protected function parseUri(string $uri): void
    {
        $parts = parse_url($uri);
        if (false === $parts || (!isset($parts['scheme']) && !isset($parts['host']) && !isset($parts['path']))) {
            throw new InvalidArgumentException('The source URI string appears to be malformed');
        }

        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = $parts['user'] ?? '';
        $this->host = $parts['host'] ?? '';

        if (isset($parts['port'])) {
            $this->port = $parts['port'];
        } else {
            // Set default port based on scheme
            $this->port = $this->allowedSchemes[$this->scheme] ?? null;
        }

        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '/';
        $this->query = isset($parts['query']) ? $this->filterQuery($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';

        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @return bool
     */
    protected function isNonStandardPort(): bool
    {
        return !in_array($this->port, $this->allowedSchemes);
    }

    /**
     * Filters the scheme to ensure it is a valid scheme.
     *
     * @param string $scheme Scheme name.
     *
     * @return string Filtered scheme.
     */
    protected function filterScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if (empty($scheme)) {
            return '';
        }

        if (!array_key_exists($scheme, $this->allowedSchemes)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
                $scheme,
                implode(', ', array_keys($this->allowedSchemes))
            ));
        }

        return $scheme;
    }

    /**
     * Filters the path of a URI to ensure it is properly encoded.
     *
     * @param string $path
     * @return string
     */
    protected function filterPath(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $path
        );
    }

    /**
     * Filter a query string to ensure it is propertly encoded.
     *
     * Ensures that the values in the query string are properly urlencoded.
     *
     * see: http://php.net/manual/en/function.parse-str.php#119484
     *
     * @param string $query
     * @return string
     */
    protected function filterQuery(string $query): string
    {
        // Remove leading ? if present
        $query = ltrim($query, '?');

        $this->queryParams = [];

        // Use parse_str for simpler query parsing
        parse_str($query, $this->queryParams);

        return http_build_query($this->queryParams);
    }

    /**
     * Filter a fragment value to ensure it is properly encoded.
     *
     * @param string $fragment
     * @return string
     */
    protected function filterFragment(string $fragment): string
    {
        if (!empty($fragment) && str_starts_with($fragment, '#')) {
            $fragment = substr($fragment, 1);
        }

        // Check if fragment contains query-like parameters (e.g., fragment?param=value&param2=value2)
        if (str_contains($fragment, '?')) {
            // Initialize fragment params
            $this->fragmentParams = [];

            $pos = strpos($fragment, '?');
            $fragmentPart = substr($fragment, 0, $pos);
            $queryPart = substr($fragment, $pos + 1);

            // Parse query part like filterQuery does
            parse_str($queryPart, $this->fragmentParams);

            // Rebuild fragment with parameters as query string
            $fragment = $fragmentPart . '?' . http_build_query($this->fragmentParams);
        } else {
            // Process fragment normally if it doesn't contain query parameters
            $fragment = $this->filterQueryOrFragment($fragment);
        }

        return $fragment;
    }

    /**
     * Filter a query string key or value, or a fragment.
     *
     * @param string $value
     * @return string
     */
    protected function filterQueryOrFragment(string $value): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . '!\$&\'\(\)*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $value
        );
    }

    /**
     * URL encode a character returned by a regex.
     *
     * @param array $matches
     * @return string
     */
    protected function urlEncodeChar(array $matches): string
    {
        return rawurlencode($matches[0]);
    }
}
