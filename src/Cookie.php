<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
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
     * @param        $name
     * @param null $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function __construct(
        $name,
        $value = null,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = null
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
    public function getName()
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
     * @return null|string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @return string
     */
    public function asString()
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
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @param $name
     * @param null $value
     * @param null $expire
     * @param null $path
     * @param null $domain
     * @param null $secure
     * @param null $httpOnly
     * @return Cookie
     */
    public static function normalizer(
        $name,
        $value = null,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = null
    ) {
        return new static($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}