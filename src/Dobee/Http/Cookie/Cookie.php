<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/28
 * Time: 下午3:57
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http\Cookie;

/**
 * Class Cookie
 * @package Dobee\Http\Cookie
 */
class Cookie implements CookieInterface
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
     * @var int
     */
    protected $expire;

    /**
     * @var string
     */
    protected $path = '/';

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var bool
     */
    protected $httpOnly = false;

    /**
     * @var array
     */
    protected $serialized = false;

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $force
     */
    public function __construct(
        $name,
        $value,
        $expire = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httpOnly = false,
        $force = false
    )
    {
        $this->name = $name;
        $this->unserialize($value);
        if (!$this->serialized || $force) {
            $this->serialized = null;
            $this->value    = $value;
            $this->expire   = $expire;
            $this->path     = $path;
            $this->domain   = $domain;
            $this->secure   = $secure;
            $this->httpOnly = $httpOnly;
        }

        setcookie(
            $this->name,
            $this->serialize(),
            $this->expire,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @param int $expire
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path = "/")
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure = false)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @param boolean $http_only
     * @return $this
     */
    public function setHttpOnly($http_only = false)
    {
        $this->httpOnly = $http_only;

        return $this;
    }

    /**
     * @param $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        setcookie($this->name, null, -1, $this->path);

        $this->serialized = null;

        return isset($_COOKIE[$this->name]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            [
                'value'     => $this->value,
                'expire'    => $this->expire,
                'path'      => $this->path,
                'domain'    => $this->domain,
                'secure'    => $this->secure,
                'httpOnly'  => $this->httpOnly,
            ]
        );
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     * @return void
     */
    public function unserialize($serialized = null)
    {
        $serialized = @unserialize($serialized);
        if ($serialized) {
            $this->serialized = true;
            $this->value    = $serialized['value'];
            $this->expire   = $serialized['expire'];
            $this->path     = $serialized['path'];
            $this->domain   = $serialized['domain'];
            $this->secure   = $serialized['secure'];
            $this->httpOnly = $serialized['httpOnly'];
        }
    }
}