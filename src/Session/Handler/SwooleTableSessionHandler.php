<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

use FastD\Http\Session\SessionHandlerInterface;
use Swoole\Table;

/**
 * Shared-memory session handler for the Swoole runtime.
 *
 * Uses a {@see Table} with an expiration column so worker processes can share
 * session state without touching the filesystem. Expired rows are never
 * returned and are removed lazily on access.
 */
class SwooleTableSessionHandler implements SessionHandlerInterface
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
}
