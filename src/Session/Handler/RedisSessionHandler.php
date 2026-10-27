<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

use FastD\Http\Session\SessionGcInterface;
use FastD\Http\Session\SessionHandlerInterface;
use Redis;

/**
 * Redis-backed session handler.
 *
 * Sessions are JSON strings under a configurable prefix and expire through
 * Redis TTL. Redis handles expiration, so gc() is a no-op kept for
 * SessionGcInterface compatibility.
 *
 * The handler intentionally depends on \Redis directly; external callers can
 * use SessionHandlerInterface to substitute other storage implementations.
 */
final class RedisSessionHandler implements SessionHandlerInterface, SessionGcInterface
{
    public function __construct(
        private Redis $redis,
        private int $ttl = 1440,
        private string $prefix = 'fastd:session:',
        private int $maxDataSize = 8192,
    ) {
    }

    public function read(string $sessionId): array
    {
        $content = $this->redis->get($this->key($sessionId));

        if (!is_string($content)) {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public function write(string $sessionId, array $data): bool
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($content === false || strlen($content) > $this->maxDataSize) {
            return false;
        }

        return (bool) $this->redis->setex($this->key($sessionId), $this->ttl, $content);
    }

    public function destroy(string $sessionId): bool
    {
        return (int) $this->redis->del($this->key($sessionId)) > 0;
    }

    public function exists(string $sessionId): bool
    {
        return (bool) $this->redis->exists($this->key($sessionId));
    }

    /**
     * Redis expires keys by TTL; no explicit sweep is required.
     */
    public function gc(int $maxLifetime = 0): int
    {
        return 0;
    }

    private function key(string $sessionId): string
    {
        return $this->prefix . $sessionId;
    }
}
