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
    protected $storage;

    public function __construct()
    {
        $redis = new Redis();

        $redis->connect('11.11.11.44', 6379);

        $this->storage = $redis;
    }

    /**
     * @return bool
     */
    public function isExpire($name)
    {
        return $this->storage->ttl(self::KEY_PREFIX . $name);
    }

    /**
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
        $this->storage->get(self::KEY_PREFIX . $name);
    }

    /**
     * @param $name
     * @param $value
     * @param $ttl
     * @return bool
     */
    public function set($name, $value, $ttl = null)
    {
        $this->storage->set(self::KEY_PREFIX . $name, $value, $ttl ?? 3600);
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

$session = new \FastD\Http\Session\Session(new RedisStorage());

//$session->set('name', 'janhuang');
//$session->set('age', 18);
//print_r($session->all());

//echo $session->get('name');