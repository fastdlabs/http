<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

/**
 * Class Uri
 *
 * @package FastD\Http
 */
class Uri implements UriInterface
{
    /**
     * Sub-delimiters used in query strings and fragments.
     *
     * @const string
     */
    const CHAR_SUB_DELIMITERS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters used in paths, query strings, and fragments.
     *
     * @const string
     */
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    /**
     * @var int[] Array indexed by valid scheme names to their corresponding ports.
     */
    protected $allowedSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * @var string
     */
    protected $scheme = '';

    /**
     * @var string
     */
    protected $userInfo = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    protected $relationPath = '/';

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var string
     */
    protected $fragment = '';

    /**
     * generated uri string cache
     *
     * @var string|null
     */
    protected $uriString;

    /**
     * @param string $uri
     * @throws InvalidArgumentException on non-string $uri argument
     */
    public function __construct($uri = '')
    {
        if (!empty($uri)) {
            $this->parseUri($uri);
        }
    }

    /**
     * Operations to perform on clone.
     *
     * Since cloning usually is for purposes of mutation, we reset the
     * $uriString property so it will be re-calculated.
     */
    public function __clone()
    {
        $this->uriString = null;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        if (null !== $this->uriString) {
            return $this->uriString;
        }

        $this->uriString = $this->createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->getPath(), // Absolute URIs should use a "/" for an empty path
            $this->query,
            $this->fragment
        );

        return $this->uriString;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority()
    {
        if (empty($this->host)) {
            return '';
        }

        $authority = $this->host;
        if (!empty($this->userInfo)) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->isNonStandardPort()) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getRelationPath()
    {
        return $this->relationPath;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param string $scheme
     * @return Uri
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);

        if ($scheme === $this->scheme) {
            // Do nothing if no change was made.
            return $this;
        }

        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @param string $user
     * @param null $password
     * @return Uri
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password) {
            $info .= ':' . $password;
        }

        if ($info === $this->userInfo) {
            // Do nothing if no change was made.
            return $this;
        }

        $this->userInfo = $info;

        return $this;
    }

    /**
     * @param string $host
     * @return Uri
     */
    public function withHost($host)
    {
        if ($host === $this->host) {
            // Do nothing if no change was made.
            return $this;
        }

        $this->host = $host;

        return $this;
    }

    /**
     * @param int|null $port
     * @return Uri
     */
    public function withPort($port)
    {
        if (!(is_integer($port) || (is_string($port) && is_numeric($port)))) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%s" specified; must be an integer or integer string',
                (is_object($port) ? get_class($port) : gettype($port))
            ));
        }

        $port = (int)$port;

        if ($port === $this->port) {
            // Do nothing if no change was made.
            return $this;
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%d" specified; must be a valid TCP/UDP port',
                $port
            ));
        }

        $this->port = $port;

        return $this;
    }

    /**
     * @param string $path
     * @return Uri
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }

        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }

        if (strpos($path, '#') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }

        $path = $this->filterPath($path);

        if ($path === $this->path) {
            // Do nothing if no change was made.
            return $this;
        }

        $this->path = $path;

        return $this;
    }

    /**
     * @param string $query
     * @return Uri
     */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException(
                'Query string must be a string'
            );
        }

        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException(
                'Query string must not include a URI fragment'
            );
        }

        $query = $this->filterQuery($query);

        if ($query === $this->query) {
            // Do nothing if no change was made.
            return $this;
        }

        $this->query = $query;

        return $this;
    }

    /**
     * @param string $fragment
     * @return Uri
     */
    public function withFragment($fragment)
    {
        $fragment = $this->filterFragment($fragment);

        if ($fragment === $this->fragment) {
            // Do nothing if no change was made.
            return $this;
        }

        $this->fragment = $fragment;

        return $this;
    }

    /**
     * Parse a URI into its parts, and set the properties
     *
     * @param $uri
     */
    protected function parseUri($uri)
    {
        $parts = parse_url($uri);

        if (false === $parts) {
            throw new InvalidArgumentException(
                'The source URI string appears to be malformed'
            );
        }

        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = isset($parts['user']) ? $parts['user'] : '';
        $this->host = isset($parts['host']) ? $parts['host'] : '';
        if (isset($parts['port'])) {
            $this->port = $parts['port'];
        } elseif('https' === $this->scheme) {
            $this->port = 443;
        } else {
            $this->port = 80;
        }
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '/';
        $this->query = isset($parts['query']) ? $this->filterQuery($parts['query']) : [];
        $this->fragment = isset($parts['fragment']) ? $this->filterFragment($parts['fragment']) : '';

        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }

        if (false !== $index = strpos($uri, '.php')) {
            $this->relationPath = substr($uri, ($index + 4));
            if (empty($this->relationPath)) {
                $this->relationPath = '/';
            }
        }
    }

    /**
     * Create a URI string from its various parts
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param array $query
     * @param string $fragment
     * @return string
     */
    public function createUriString($scheme, $authority, $path, array $query, $fragment)
    {
        $uri = '';

        if (!empty($scheme)) {
            $uri .= sprintf('%s://', $scheme);
        }

        if (!empty($authority)) {
            $uri .= $authority;
        }

        if ($path) {
            if (empty($path) || '/' !== substr($path, 0, 1)) {
                $path = '/' . $path;
            }

            $uri .= $path;
        }

        if ($query) {
            $uri .= sprintf('?%s', http_build_query($query));
        }

        if ($fragment) {
            $uri .= sprintf('#%s', $fragment);
        }

        return $uri;
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @return bool
     */
    protected function isNonStandardPort()
    {
        if (in_array((int) $this->port, $this->allowedSchemes)) {
            return false;
        }

        return true;
    }

    /**
     * Filters the scheme to ensure it is a valid scheme.
     *
     * @param string $scheme Scheme name.
     *
     * @return string Filtered scheme.
     */
    protected function filterScheme($scheme)
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
    protected function filterPath($path)
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
     * @return array
     */
    protected function filterQuery($query)
    {
        $queryInfo = [];

        foreach (explode('&', $query) as $part) {
            list($name, $value) = explode('=', (false === strpos($part, '=') ? "{$part}=" : $part), 2);

            $name = rawurldecode($name);
            $value = rawurldecode($value);

            if (0 === preg_match_all('/\[([^\]]*)\]/m', $name, $matches)) {
                $queryInfo[$name] = $value;
                continue;
            }

            $name   = substr($name, 0, strpos($name, '['));
            $keys   = array_merge([$name], $matches[1]);
            $target = &$queryInfo;

            foreach ($keys as $index) {
                if ('' === $index) {
                    $target = &$target[];
                } else {
                    $target = &$target[$index];
                }
            }

            $target = $value;
        }

        return $queryInfo;
    }

    /**
     * Split a query value into a key/value tuple.
     *
     * @param string $value
     * @return array A value with exactly two elements, key and value
     */
    protected function splitQueryValue($value)
    {
        $data = explode('=', $value, 2);
        if (1 === count($data)) {
            $data[] = null;
        }
        return $data;
    }

    /**
     * Filter a fragment value to ensure it is properly encoded.
     *
     * @param null|string $fragment
     * @return string
     */
    protected function filterFragment($fragment)
    {
        if (null === $fragment) {
            $fragment = '';
        }

        if (!empty($fragment) && strpos($fragment, '#') === 0) {
            $fragment = substr($fragment, 1);
        }

        return $this->filterQueryOrFragment($fragment);
    }

    /**
     * Filter a query string key or value, or a fragment.
     *
     * @param string $value
     * @return string
     */
    protected function filterQueryOrFragment($value)
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
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
    protected function urlEncodeChar(array $matches)
    {
        return rawurlencode($matches[0]);
    }
}