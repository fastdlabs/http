<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/9/2
 * Time: 下午4:34
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\Session\Storage;

class RedisStorage implements SessionStorageInterface
{
    protected $redis;

    protected $ttl = 7200;

    protected $prefix = 'PHPSESSID:';

    public function __construct($host, $port, $auth = null, $timeout = 2)
    {
        $this->redis = new \Redis();
        $this->redis->connect($host, $port, $timeout);
        if (null !== $auth) {
            $this->redis->auth($auth);
        }
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     * @return $this
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function get($name)
    {
        $name = $this->getPrefix() . $name;
        if (false === $this->redis->exists($name)) {
            return null;
        }
        return $this->redis->get($name);
    }

    public function set($name, $value)
    {
        $name = $this->getPrefix() . $name;
        $this->redis->set($name, $value);
        return $this->redis->expire($name, $this->getTtl());
    }

    public function exists($name)
    {
        return $this->redis->exists($this->getPrefix() . $name);
    }

    public function remove($name)
    {
        return $this->redis->del($this->getPrefix() . $name);
    }
}