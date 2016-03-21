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

namespace FastD\Http\Attribute;

/**
 * Class CookiesAttribute
 *
 * @package FastD\Http\Attribute
 */
class CookiesAttribute extends Attribute
{
    /**
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            setcookie($name, null, -1, '/');
        }
        return parent::remove($name);
    }

    /**
     * {@inheritdoc}
     *
     * @param        $name
     * @param null   $value
     * @param int    $expire
     * @param string $path
     * @param null   $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return CookiesAttribute
     */
    public function setCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = false)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        return parent::set($name, $value);
    }

    /**
     * @param $name
     * @return mixed
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