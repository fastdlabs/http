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

/**
 * Class CookiesBag
 *
 * @package FastD\Http
 */
class CookiesBag extends Bag
{
    /**
     * CookiesAttribute constructor.
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = new Cookie($key, $value, null, null, null, null, null, false);
        }

        parent::__construct($parameters);
    }

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
     * @param int|null    $expire
     * @param string $path
     * @param null   $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return CookiesAttribute
     */
    public function set($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        return parent::set($name, new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly));
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