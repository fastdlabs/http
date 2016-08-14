<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Bag;

use FastD\Http\Cookie;

/**
 * Class CookiesBag
 *
 * @package FastD\Http\Bag
 */
class CookieBag extends Bag
{
    /**
     * CookiesAttribute constructor.
     * @param array $bag
     */
    public function __construct(array $bag = [])
    {
        foreach ($bag as $key => $value) {
            $bag[$key] = new Cookie($key, $value, null, null, null, null, null);
        }

        parent::__construct($bag);
    }

    /**
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }

        return parent::remove($name);
    }

    /**
     * @param $name
     * @param null $value
     * @param null $expire
     * @param null $path
     * @param null $domain
     * @param null $secure
     * @param null $httpOnly
     * @return $this
     */
    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        parent::set($name, new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param $name
     * @param bool $raw
     * @param null $callback
     * @return Cookie
     */
    public function get($name, $raw = false, $callback = null)
    {
        return parent::get($name, $raw, $callback);
    }

    /**
     * @param $name
     * @return bool
     */
    public function clear($name)
    {
        return $this->remove($name);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function clearAll()
    {
        foreach ($this->all() as $key => $value) {
            $this->remove($key);
        }

        return $this;
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