<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

/**
 * Minimal Redis operations required by {@see RedisSessionHandler}.
 *
 * Implementations can wrap phpredis, Predis or a test double.
 */
interface RedisClientInterface
{
    public function get(string $key): string|false;

    public function setex(string $key, int $ttl, string $value): bool;

    public function del(string $key): int;

    public function exists(string $key): int;
}
