<?php

declare(strict_types=1);

namespace FastD\Http\Session;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * Runtime-agnostic session contract.
 *
 * @extends ArrayAccess<string, mixed>
 * @extends IteratorAggregate<string, mixed>
 */
interface SessionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The session id used as the storage key and emitted in the session cookie.
     */
    public function getId(): string;

    /**
     * Whether the session is active; false once invalidated.
     */
    public function isStarted(): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function remove(string $key): void;

    /**
     * @return array<string, mixed> the user data bag
     */
    public function all(): array;

    /**
     * Remove all user data from the session.
     */
    public function clear(): void;

    /**
     * Stage a value that is only available on the next request, then consumed.
     */
    public function flash(string $key, mixed $value): void;

    /**
     * Read and consume a flashed value; null when nothing was flashed.
     */
    public function getFlash(string $key, mixed $default = null): mixed;

    /**
     * Csrf token bound to this session, generated on first access.
     */
    public function getToken(): string;

    /**
     * Replace the csrf token with a freshly generated one.
     */
    public function regenerateToken(): string;

    /**
     * Constant-time check of a csrf token against the token bound to this session.
     */
    public function isTokenValid(string $token): bool;

    /**
     * Destroy the session: clear all data, delete the storage entry and stop the session.
     */
    public function invalidate(): void;

    /**
     * Rotate the session id while keeping the current data.
     *
     * The previous id is deleted from storage; the caller is responsible for
     * persisting the session under the new id (the middleware does this for
     * every response). Call it after a successful login or privilege change to
     * mitigate session fixation.
     */
    public function regenerateId(): string;

    /**
     * Persist the current state through the handler.
     *
     * @return bool false when the session is no longer started or the handler rejected the write
     */
    public function save(): bool;
}
