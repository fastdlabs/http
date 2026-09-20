<?php

declare(strict_types=1);

namespace FastD\Http\Session;

/**
 * Storage contract for session data.
 *
 * Handlers own persistence concerns only (TTL, atomicity, storage limits).
 * Session semantics (id generation, flash bag, csrf token, invalidation)
 * belong to {@see Session}.
 *
 * This interface deliberately does not extend PHP's native
 * \SessionHandlerInterface: the native contract speaks serialized strings,
 * while sessions here are handled as PHP arrays without touching the native
 * session module. See .qoder/plans/adr-session-handler-contract.md (ADR-001).
 */
interface SessionHandlerInterface
{
    /**
     * Read the session payload by id.
     *
     * @return array<string, mixed> empty array when the session does not exist or has expired
     */
    public function read(string $sessionId): array;

    /**
     * Persist the session payload by id.
     *
     * @param array<string, mixed> $data
     */
    public function write(string $sessionId, array $data): bool;

    /**
     * Remove the session payload by id.
     *
     * @return bool false when the session did not exist
     */
    public function destroy(string $sessionId): bool;

    /**
     * Whether an unexpired session exists for the given id.
     */
    public function exists(string $sessionId): bool;
}
