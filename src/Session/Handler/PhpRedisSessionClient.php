<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

/**
 * phpredis adapter for RedisSessionHandler.
 *
 * Usage:
 *   new PhpRedisSessionClient(new \Redis())
 */
final class PhpRedisSessionClient implements RedisClientInterface
{
    public function __construct(private mixed $redis)
    {
    }

    public function get(string $key): string|false
    {
        $value = $this->redis->get($key);

        return is_string($value) || $value === false ? $value : false;
    }

    public function setex(string $key, int $ttl, string $value): bool
    {
        return (bool) $this->redis->setex($key, $ttl, $value);
    }

    public function del(string $key): int
    {
        return (int) $this->redis->del($key);
    }

    public function exists(string $key): int
    {
        return (int) $this->redis->exists($key);
    }
}
