<?php
/**
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
     * CookieBag constructor.
     *
     * @param array $bag
     */
    public function __construct(array $bag = [])
    {
        foreach ($bag as $key => $value) {
            $bag[$key] = new Cookie($key, $value);
        }

        parent::__construct($bag);
    }

    /**
     * @param $name
     * @param $value
     * @param $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return $this
     */
    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        parent::set($name, new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly));

        return $this;
    }

    /**
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
            $cookies .= $cookie->asString();
        }

        return $cookies;
    }
}