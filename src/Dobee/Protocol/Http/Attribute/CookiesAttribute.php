<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:55
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\Attribute;

use Dobee\Protocol\Attribute\Attribute;
use Dobee\Protocol\Http\Cookie\Cookie;

/**
 * Class CookiesAttribute
 *
 * @package Dobee\Protocol\Http\Attribute
 */
class CookiesAttribute extends Attribute
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $name => $value) {
            $this->setCookie($name, $value, 0, '/', null, false, true, false);
        }
    }

    /**
     * @param        $name
     * @param Cookie $cookie
     * @return $this
     */
    public function set($name, $cookie)
    {
        return parent::set($name, $cookie);
    }

    /**
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        setcookie($name, null, -1);
        return parent::remove($name);
    }

    /**
     * @param        $name
     * @param null   $value
     * @param int    $expire
     * @param string $path
     * @param null   $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @param bool   $force
     * @return CookiesAttribute
     */
    public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true, $force = true)
    {
        return $this->set($name, new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $force));
    }

    /**
     * @param $name
     * @return Cookie
     */
    public function getCookie($name)
    {
        return parent::get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCookie($name)
    {
        return parent::has($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function clearCookie($name)
    {
        return $this->remove($name);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $cookies = '';

        foreach ($this->all() as $cookie) {
            $cookies .= $cookie;
        }

        return $cookies;
    }
}