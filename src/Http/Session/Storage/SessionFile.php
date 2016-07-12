<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/5/11
 * Time: 下午4:51
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\Session\Storage;

use FastD\Storage\CacheInterface;

/**
 * Class SessionRedis
 *
 * @package FastD\Http\Session\Storage
 */
class SessionFile implements SessionStorageInterface
{
    /**
     * @var CacheInterface
     */
    protected $storage;

    /**
     * SessionRedis constructor.
     * @param CacheInterface $cacheInterface
     */
    public function __construct(CacheInterface $cacheInterface)
    {
        $this->storage = $cacheInterface;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isExpire($name)
    {
        return $this->storage->ttl(self::KEY_PREFIX . $name);
    }

    /**
     * @param $name
     * @param $ttl
     * @return mixed
     */
    public function ttl($name, $ttl)
    {
        return $this->storage->expire(self::KEY_PREFIX . $name, $ttl);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->storage->get(self::KEY_PREFIX . $name);
    }

    /**
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    public function set($name, $value, $ttl = null)
    {
        return $this->storage->set(self::KEY_PREFIX . $name, $value, $ttl ?? 3600);
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return $this->storage->expire(self::KEY_PREFIX . $name, 1);
    }

    /**
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        return $this->storage->del($name);
    }
}