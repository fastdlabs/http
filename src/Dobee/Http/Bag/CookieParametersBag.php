<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:28
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

/**
 * Class CookieParametersBag
 *
 * @package Dobee\Http\Bag
 */
class CookieParametersBag
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @param array $cookie
     */
    public function __construct(array $cookie = null)
    {
        if (null === $cookie) {
            $cookie = $_COOKIE;
        }

        $this->parameters = $cookie;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->parameters['PHPSESSID'];
    }

    /**
     * @param        $key
     * @param        $value
     * @param int    $expire
     * @param string $path
     * @param null   $domain
     * @param bool   $secure
     * @param bool   $http_only
     * @return $this
     */
    public function set($key, $value, $expire = 0, $path = '/', $domain = null, $secure = false, $http_only = false)
    {
        setcookie($key, $this->encode($value), $expire, $path, $domain, $secure, $http_only);

        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException(sprintf('Cookie key: \'%s\' is undefined.', $key));
        }

        return $this->parameters[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     * @return $this
     */
    public function clearAll()
    {
        $_COOKIE = null;

        $this->parameters = array(
            'PHPSESSID' => $this->getSessionId()
        );

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function clear($key)
    {
        setcookie($key, null, -1);

        unset($_COOKIE[$key]);

        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }

        return $this;
    }

    /**
     * @param      $value
     * @param null $func
     * @return mixed
     */
    public function encode($value, $func = null)
    {
        if (is_callable($func)) {
            $value = $func($value);
        }

        return $value;
    }
}