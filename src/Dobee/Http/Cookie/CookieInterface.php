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
 * @package Dobee\Http
 */
interface CookieInterface
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

    public function setValue($value);

    public function getValue();

    public function setExpire($time);

    public function getExpire();

    public function setPath($path = '/');

    public function getPath();

    public function setDomain($domain);

    public function getDomain();

    public function setSecure($secure = false);

    public function getSecure();

    public function setHttpOnly($httpOnly = false);

    public function getHttpOnly();
}