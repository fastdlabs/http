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
use Dobee\Http\Cookie\CookiesException;

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
    private $cookies = array();

    /**
     * @param array $cookie
     */
    public function __construct(array $cookie = null)
    {

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
     * @throws CookiesException
     */
    public function getCookie($name = null)
    {
        if (!$this->hasCookie($name)) {
            throw new CookiesException(sprintf('Cookie "%s" is undefined.', $name));
        }

        return $this->cookies[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCookie($name)
    {
        return isset($this->cookies[$name]);
    }

    public function removeCookie($name)
    {
        unset($this->cookies[$name]);
    }

    public function getHandler()
    {

    }
}