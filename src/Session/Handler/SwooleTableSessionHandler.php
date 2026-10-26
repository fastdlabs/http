<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

use FastD\Http\Session\SessionGcInterface;
use FastD\Http\Session\SessionHandlerInterface;
use Swoole\Table;

/**
 * Shared-memory session handler for the Swoole runtime.
 *
 * Uses a {@see Table} with an expiration column so worker processes can share
 * session state without touching the filesystem. Expired rows are never
 * returned and are removed lazily on access.
 *
 * Payloads are JSON encoded before being stored in the `data` column, so
 * session values must be JSON serialisable (the same rule as the file
 * handler). Writes that cannot be encoded or that exceed `maxDataSize` are
 * rejected with `false`; a full table also makes `Table::set()` return false.
 */
class SwooleTableSessionHandler implements SessionHandlerInterface, SessionGcInterface
{
    private Table $table;

    public function __construct(
        private int $ttl = 1440,
        int $size = 4096,
        private int $maxDataSize = 8192,
    ) {
        $this->table = new Table($size);
        $this->table->column('data', Table::TYPE_STRING, $maxDataSize);
        $this->table->column('expires', Table::TYPE_INT);
        $this->table->create();
    }

    public function read(string $sessionId): array
    {
        $row = $this->table->get($sessionId);

        if (!is_array($row)) {
            return [];
        }

        if ((int) $row['expires'] <= time()) {
            $this->table->del($sessionId);

            return [];
        }

        $data = json_decode((string) $row['data'], true);

        return is_array($data) ? $data : [];
    }

    public function write(string $sessionId, array $data): bool
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($content === false || strlen($content) > $this->maxDataSize) {
            return false;
        }

        return $this->table->set($sessionId, [
            'data' => $content,
            'expires' => time() + $this->ttl,
        ]);
    }

    public function destroy(string $sessionId): bool
    {
        if (!$this->table->exists($sessionId)) {
            return false;
        }

        return $this->table->del($sessionId);
    }

    public function exists(string $sessionId): bool
    {
        $row = $this->table->get($sessionId);

        if (!is_array($row)) {
            return false;
        }

        if ((int) $row['expires'] <= time()) {
            $this->table->del($sessionId);

            return false;
        }

        return true;
    }

    public function gc(int $maxLifetime = 0): int
    {
        $now = time();
        $expired = [];

        // Collect first: deleting while iterating a Swoole\Table is unsafe.
        foreach ($this->table as $key => $row) {
            if ((int) ($row['expires'] ?? 0) <= $now) {
                $expired[] = (string) $key;
            }
        }

        $removed = 0;

        foreach ($expired as $key) {
            if ($this->table->del($key)) {
                $removed++;
            }
        }

        return $removed;
    }
}
