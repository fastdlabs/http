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

use Dobee\Http\Cookie\Cookie;
use Dobee\Http\Cookie\CookieInterface;

/**
 * Class CookieParametersBag
 *
 * @package Dobee\Http\Bag
 */
class CookieParametersBag implements \Iterator, \Countable
{
    /**
     * @var Cookie[]|array
     */
    private $cookies = array();

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @param array $cookie
     */
    public function __construct(array $cookie)
    {
        $this->parameters = $cookie;
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return $this
     */
    public function setCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
    {
        $this->cookies[$name] = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);

        return $this;
    }

    /**
     * @param null|string $name
     * @return CookieInterface
     */
    public function getCookie($name = null)
    {
        if (!isset($this->cookies[$name])) {
            if (!isset($this->parameters[$name])) {
                throw new \InvalidArgumentException(sprintf('Cookie "%s" is undefined.', $name));
            }

            $this->setCookie($name, $this->parameters[$name]);
        }


        return $this->cookies[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCookie($name)
    {
        return isset($this->cookies[$name]) || isset($this->parameters[$name]);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function removeCookie($name)
    {
        return $this->getCookie($name)->clear();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->cookies);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->cookies);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->cookies);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->cookies[$this->key()]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->cookies);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->cookies);
    }
}