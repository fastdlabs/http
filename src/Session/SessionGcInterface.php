<?php

declare(strict_types=1);

namespace FastD\Http\Session;

/**
 * Optional garbage-collection capability for session handlers.
 *
 * Handlers that can sweep expired entries proactively implement this
 * interface in addition to {@see SessionHandlerInterface}. The middleware
 * invokes it probabilistically (see SessionMiddleware::__construct()) so the
 * behaviour stays opt-in and storage specific.
 */
interface SessionGcInterface
{
    /**
     * Remove expired sessions.
     *
     * @param int $maxLifetime override the handler TTL in seconds; 0 keeps the handler default
     * @return int number of removed sessions
     */
    public function gc(int $maxLifetime = 0): int;
}
