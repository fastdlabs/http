<?php

declare(strict_types=1);

namespace FastD\Http\Session;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * Runtime-agnostic session object.
 *
 * Owns session semantics only; persistence is delegated to a
 * {@see SessionHandlerInterface} so the same session code runs under
 * CGI and Swoole without touching PHP's native session module.
 */
class Session implements SessionInterface
{
    /**
     * Reserved payload key holding flash data staged for the next request.
     */
    public const FLASH_KEY = '__flash';

    /**
     * Reserved payload key holding the csrf token.
     */
    public const TOKEN_KEY = '__token';

    /**
     * @var array<string, mixed> user data bag
     */
    private array $data = [];

    /**
     * @var array<string, mixed> flash staged by this request, available on the next one
     */
    private array $newFlash = [];

    /**
     * @var array<string, mixed> flash inherited from the previous request, consumed on read
     */
    private array $flash = [];

    private ?string $token = null;

    private bool $started = true;

    /**
     * @param array<string, mixed> $payload persisted payload to hydrate from
     */
    public function __construct(
        private SessionHandlerInterface $handler,
        private string $id,
        array $payload = [],
    ) {
        if ($payload === []) {
            return;
        }

        $flash = $payload[self::FLASH_KEY] ?? null;
        $token = $payload[self::TOKEN_KEY] ?? null;

        unset($payload[self::FLASH_KEY], $payload[self::TOKEN_KEY]);

        $this->data = $payload;
        $this->flash = is_array($flash) ? $flash : [];
        $this->token = is_string($token) ? $token : null;
    }

    /**
     * Start a session for the given id, hydrating stored data when present.
     */
    public static function start(SessionHandlerInterface $handler, string $id): self
    {
        return new self($handler, $id, $handler->read($id));
    }

    public static function generateId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function clear(): void
    {
        $this->data = [];
    }

    public function flash(string $key, mixed $value): void
    {
        $this->newFlash[$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->flash)) {
            return $default;
        }

        $value = $this->flash[$key];
        unset($this->flash[$key]);

        return $value;
    }

    public function getToken(): string
    {
        return $this->token ??= bin2hex(random_bytes(32));
    }

    public function regenerateToken(): string
    {
        return $this->token = bin2hex(random_bytes(32));
    }

    public function isTokenValid(string $token): bool
    {
        return $this->token !== null && hash_equals($this->token, $token);
    }

    public function invalidate(): void
    {
        $this->data = [];
        $this->flash = [];
        $this->newFlash = [];
        $this->token = null;
        $this->started = false;

        $this->handler->destroy($this->id);
    }

    public function regenerateId(): string
    {
        $previousId = $this->id;
        $this->id = self::generateId();

        if ($this->started) {
            $this->handler->destroy($previousId);
        }

        return $this->id;
    }

    public function save(): bool
    {
        if (!$this->started) {
            return false;
        }

        return $this->handler->write($this->id, $this->payload());
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($this->normalizeKey($offset));
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($this->normalizeKey($offset));
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($this->normalizeKey($offset), $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($this->normalizeKey($offset));
    }

    /**
     * @return array<string, mixed> the payload persisted through the handler
     */
    private function payload(): array
    {
        $payload = $this->data;
        unset($payload[self::FLASH_KEY], $payload[self::TOKEN_KEY]);

        if ($this->newFlash !== []) {
            $payload[self::FLASH_KEY] = $this->newFlash;
        }

        if ($this->token !== null) {
            $payload[self::TOKEN_KEY] = $this->token;
        }

        return $payload;
    }

    private function normalizeKey(mixed $offset): string
    {
        if (!is_string($offset) && !is_int($offset)) {
            throw new InvalidArgumentException('Session keys must be strings.');
        }

        return (string) $offset;
    }
}
