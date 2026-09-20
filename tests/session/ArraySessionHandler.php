<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Session\SessionHandlerInterface;

/**
 * In-memory session handler test double.
 */
class ArraySessionHandler implements SessionHandlerInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    public array $store = [];

    public int $writeCalls = 0;

    public int $destroyCalls = 0;

    public function read(string $sessionId): array
    {
        return $this->store[$sessionId] ?? [];
    }

    public function write(string $sessionId, array $data): bool
    {
        $this->writeCalls++;
        $this->store[$sessionId] = $data;

        return true;
    }

    public function destroy(string $sessionId): bool
    {
        $this->destroyCalls++;

        if (!isset($this->store[$sessionId])) {
            return false;
        }

        unset($this->store[$sessionId]);

        return true;
    }

    public function exists(string $sessionId): bool
    {
        return isset($this->store[$sessionId]);
    }
}
