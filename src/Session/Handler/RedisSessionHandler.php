<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

use FastD\Http\Session\SessionGcInterface;
use FastD\Http\Session\SessionHandlerInterface;

/**
 * Redis-backed session handler.
 *
 * Sessions are stored as JSON strings under a configurable key prefix and
 * expire through Redis TTL. Redis performs expiry itself, so gc() is a no-op
 * kept for SessionGcInterface compatibility.
 */
final class RedisSessionHandler implements SessionHandlerInterface, SessionGcInterface
{
    public function __construct(
        private RedisClientInterface $client,
        private int $ttl = 1440,
        private string $prefix = 'fastd:session:',
        private int $maxDataSize = 8192,
    ) {
    }

    public function read(string $sessionId): array
    {
        $content = $this->client->get($this->key($sessionId));

        if ($content === false) {
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

        return $this->client->setex($this->key($sessionId), $this->ttl, $content);
    }

    public function destroy(string $sessionId): bool
    {
        return $this->client->del($this->key($sessionId)) > 0;
    }

    public function exists(string $sessionId): bool
    {
        return $this->client->exists($this->key($sessionId)) > 0;
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
