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

interface SessionStorageInterface
{
    const KEY_PREFIX = 'SESS:';
    const TTL = 3600; // 1 hour

    /**
     * @param $name
     * @return mixed
     */
    public function isExpire($name);

    /**
     * @param $name
     * @param $ttl
     * @return mixed
     */
    public function ttl($name, $ttl);

    /**
     * @param $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    public function set($name, $value, $ttl = null);

    /**
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * @param $name
     * @return bool
     */
    public function remove($name);
}