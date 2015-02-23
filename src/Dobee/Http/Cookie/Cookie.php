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

namespace Dobee\Http;

/**
 * Class Cookie
 *
 * @package Dobee\Http
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
    protected $secure = false;

    /**
     * @var bool
     */
    protected $http_only = false;

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
    public function setPath($path)
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
    public function setSecure($secure)
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->http_only;
    }

    /**
     * @param boolean $http_only
     * @return $this
     */
    public function setHttpOnly($http_only)
    {
        $this->http_only = $http_only;

        return $this;
    }
}