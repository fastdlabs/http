<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/9/2
 * Time: 下午4:11
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\Session\Storage;

/**
 * Interface SessionStorageInterface
 *
 * @package FastD\Http\Session\Storage
 */
interface SessionStorageInterface
{
    /**
     * @param $ttl
     * @return $this
     */
    public function setTtl($ttl);

    /**
     * @return int
     */
    public function getTtl();

    /**
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param $name
     * @param $value
     * @return bool
     */
    public function set($name, $value);

    /**
     * @param $name
     * @return bool
     */
    public function exists($name);

    /**
     * @param $name
     * @return bool
     */
    public function remove($name);
}