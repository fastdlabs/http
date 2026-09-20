<?php

declare(strict_types=1);

namespace FastD\Http\Session;

use FastD\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 middleware bridging the session lifecycle into the request pipeline.
 *
 * Reads the session id from the request cookie, starts a {@see Session} and
 * injects it as the "session" request attribute. On the way out it persists
 * the session and emits the session cookie, or a deleting cookie when the
 * session has been invalidated.
 */
class SessionMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE = 'session';

    private const ID_PATTERN = '/^[a-f0-9]{32}$/';

    public function __construct(
        private SessionHandlerInterface $handler,
        private string $cookieName = 'FASTD_SESSID',
        private string $cookiePath = '/',
        private string $cookieDomain = '',
        private bool $cookieSecure = false,
        private bool $cookieHttpOnly = true,
        private string $cookieSameSite = 'Lax',
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = Session::start($this->handler, $this->resolveSessionId($request));

        $response = $handler->handle($request->withAttribute(self::ATTRIBUTE, $session));

        if (!$session->isStarted()) {
            return $response->withAddedHeader('Set-Cookie', (string) $this->cookie(''));
        }

        $session->save();

        return $response->withAddedHeader('Set-Cookie', (string) $this->cookie($session->getId()));
    }

    /**
     * Resolve a usable session id from the request cookie.
     *
     * Untrusted cookie values are never used as a storage key: anything that
     * is not a well formed id pointing at an existing session yields a freshly
     * generated id.
     */
    private function resolveSessionId(ServerRequestInterface $request): string
    {
        $cookies = $request->getCookieParams();
        $id = $cookies[$this->cookieName] ?? null;

        if (!is_string($id) || preg_match(self::ID_PATTERN, $id) !== 1 || !$this->handler->exists($id)) {
            return Session::generateId();
        }

        return $id;
    }

    /**
     * An empty value renders as a deleting cookie with a past expiry.
     */
    private function cookie(string $value): Cookie
    {
        return Cookie::create(
            $this->cookieName,
            $value,
            -1,
            $this->cookiePath,
            $this->cookieDomain,
            $this->cookieSecure,
            $this->cookieHttpOnly,
            $this->cookieSameSite,
        );
    }
}
