<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/21
 * Time: 下午11:52
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

class RedisStorage implements \FastD\Http\Session\SessionStorageInterface
{
    /**
     * @return bool
     */
    public function isExpire()
    {
        // TODO: Implement isExpire() method.
    }

    /**
     * @param $ttl
     * @return mixed
     */
    public function ttl($ttl)
    {
        // TODO: Implement ttl() method.
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    public function set($name, $value, $ttl = null)
    {
        // TODO: Implement set() method.
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        // TODO: Implement has() method.
    }

    /**
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        // TODO: Implement remove() method.
    }
}

$session = new \FastD\Http\Session\Session(new RedisStorage());

$session->set('name', 'janhuang');
