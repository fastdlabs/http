<?php
/**
 * Created by PhpStorm.
 * User: JanHuang
 * Date: 2015/3/1
 * Time: 13:03
 * Email: bboyjanhuang@gmail.com
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http\Cookie;

/**
 * Interface CookieInterface
 *
 * @package Dobee\Http\Cookie
 */
interface CookieInterface extends \Serializable
{
    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param $time
     * @return $this
     */
    public function setExpire($time);

    /**
     * @return int
     */
    public function getExpire();

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path = '/');

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain);

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @param bool $secure
     * @return $this
     */
    public function setSecure($secure = false);

    /**
     * @return bool
     */
    public function getSecure();

    /**
     * @param bool $httpOnly
     * @return $this
     */
    public function setHttpOnly($httpOnly = false);

    /**
     * @return bool
     */
    public function getHttpOnly();

    /**
     * @return bool
     */
    public function clear();
}