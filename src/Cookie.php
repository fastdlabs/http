<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;


/**
 * Class Cookie
 *
 * @package FastD\Http
 */
class Cookie
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $domain;

    /**
     * Default time() + $expire.
     *
     * @var int
     */
    protected $expire;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $secure;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * Cookie constructor.
     * @param string $name
     * @param null|string $value
     * @param int|null $expire
     * @param null|string $path
     * @param null|string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function __construct(
        string $name,
        ?string $value = null,
        ?int $expire = null,
        ?string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = false
    ) {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = $expire;
        $this->path = $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return boolean
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return boolean
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @return string
     */
    public function asString(): string
    {
        $str = urlencode($this->name).'=';
        if ('' === (string)$this->value) {
            $str .= 'deleted; expires='.gmdate("D, d-M-Y H:i:s T", time() - 31536001);
        } else {
            $str .= urlencode($this->value);
        }
        if ($this->expire > 0) {
            $str .= '; expires='.gmdate("D, d-M-Y H:i:s T", time () + $this->expire);
        }
        if ($this->path) {
            $str .= '; path='.$this->path;
        }
        if ($this->domain) {
            $str .= '; domain='.$this->domain;
        }
        if (true === $this->secure) {
            $str .= '; secure';
        }
        if (true === $this->httpOnly) {
            $str .= '; httponly';
        }

        return $str;
    }

    /**
     * Returns the cookie's value.
     *
     * @return string The cookie value
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @param string $name
     * @param null|string $value
     * @param int|null $expire
     * @param null|string $path
     * @param null|string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return Cookie
     */
    public static function normalizer(
        string $name,
        ?string $value = null,
        ?int $expire = null,
        ?string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = false
    ): Cookie
    {
        return new static($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}