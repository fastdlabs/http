<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

require_once __DIR__ . '/ArraySessionHandler.php';

use FastD\Http\Request\ServerRequest;
use FastD\Http\Response\Text;
use FastD\Http\Session\Session;
use FastD\Http\Session\SessionHandlerInterface;
use FastD\Http\Session\SessionInterface;
use FastD\Http\Session\SessionMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * SessionMiddleware 集成测试：请求属性注入、Cookie 生命周期与持久化。
 */
class SessionMiddlewareTest extends TestCase
{
    private const EXISTING_ID = 'a1b2c3d4e5f60718293a4b5c6d7e8f90';

    private ArraySessionHandler $handler;

    private SessionMiddleware $middleware;

    protected function setUp(): void
    {
        $this->handler = new ArraySessionHandler();
        $this->middleware = new SessionMiddleware($this->handler);
    }

    public function testExistingSessionIsReadableViaAttributeAndCookieIsUnchanged(): void
    {
        $this->handler->store[self::EXISTING_ID] = ['user' => 'jan'];

        $probe = $this->probe();
        $response = $this->middleware->process($this->requestWithCookie(self::EXISTING_ID), $probe);

        $this->assertInstanceOf(SessionInterface::class, $probe->session);
        $this->assertSame(self::EXISTING_ID, $probe->session->getId());
        $this->assertSame('jan', $probe->session->get('user'));

        $setCookie = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('FASTD_SESSID=' . self::EXISTING_ID, $setCookie);
        $this->assertStringNotContainsString('deleted', $setCookie);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testNewSessionIsCreatedAndCookieEmittedWhenNoCookiePresent(): void
    {
        $probe = $this->probe();
        $response = $this->middleware->process(new ServerRequest('GET', 'http://example.com/'), $probe);

        $this->assertInstanceOf(SessionInterface::class, $probe->session);

        $id = $probe->session->getId();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $id);
        $this->assertStringContainsString('FASTD_SESSID=' . $id, $response->getHeaderLine('Set-Cookie'));
        $this->assertArrayHasKey($id, $this->handler->store);
    }

    public function testWrittenDataIsPersistedAndReadBackOnTheNextRequest(): void
    {
        $first = $this->probe(function (SessionInterface $session): void {
            $session->set('user', 'jan');
        });
        $this->middleware->process(new ServerRequest('GET', 'http://example.com/'), $first);

        $this->assertSame(1, $this->handler->writeCalls);
        $id = $first->session->getId();
        $this->assertSame(['user' => 'jan'], $this->handler->read($id));

        $second = $this->probe();
        $this->middleware->process($this->requestWithCookie($id), $second);

        $this->assertSame('jan', $second->session->get('user'));
    }

    public function testFlashIsDeliveredOnTheNextRequestAndConsumedOnce(): void
    {
        $first = $this->probe(function (SessionInterface $session): void {
            $session->flash('notice', 'Saved');
        });
        $this->middleware->process(new ServerRequest('GET', 'http://example.com/'), $first);
        $id = $first->session->getId();

        $second = $this->probe();
        $this->middleware->process($this->requestWithCookie($id), $second);
        $this->assertSame('Saved', $second->session->getFlash('notice'));
        $this->assertNull($second->session->getFlash('notice'));

        $third = $this->probe();
        $this->middleware->process($this->requestWithCookie($id), $third);
        $this->assertNull($third->session->getFlash('notice'));
    }

    public function testInvalidatedSessionClearsDataDestroysAndEmitsDeletingCookie(): void
    {
        $this->handler->store[self::EXISTING_ID] = ['user' => 'jan'];

        $probe = $this->probe(function (SessionInterface $session): void {
            $session->invalidate();
        });
        $response = $this->middleware->process($this->requestWithCookie(self::EXISTING_ID), $probe);

        $this->assertSame(1, $this->handler->destroyCalls);
        $this->assertArrayNotHasKey(self::EXISTING_ID, $this->handler->store);
        $this->assertSame(0, $this->handler->writeCalls);
        $this->assertSame([], $probe->session->all());

        $setCookie = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('FASTD_SESSID=', $setCookie);
        $this->assertStringContainsString('deleted', $setCookie);
        $this->assertMatchesRegularExpression('/expires=([^;]+)/', $setCookie);

        preg_match('/expires=([^;]+)/', $setCookie, $matches);
        $this->assertLessThan(time(), strtotime($matches[1]));
    }

    public function testMalformedCookieValueYieldsFreshSessionWithoutException(): void
    {
        $malformed = 'not-hex; DROP TABLE sessions';

        $probe = $this->probe();
        $response = $this->middleware->process($this->requestWithCookie($malformed), $probe);

        $id = $probe->session->getId();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $id);
        $this->assertStringContainsString('FASTD_SESSID=' . $id, $response->getHeaderLine('Set-Cookie'));
        $this->assertArrayNotHasKey($malformed, $this->handler->store);
    }

    public function testUnknownSessionIdIsReplacedByFreshSession(): void
    {
        $stale = str_repeat('a', 32);

        $probe = $this->probe();
        $this->middleware->process($this->requestWithCookie($stale), $probe);

        $this->assertNotSame($stale, $probe->session->getId());
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $probe->session->getId());
    }

    public function testCustomCookieNameIsRespected(): void
    {
        $middleware = new SessionMiddleware($this->handler, 'MYSESSID');
        $id = str_repeat('b', 32);
        $this->handler->store[$id] = ['user' => 'jan'];

        $request = (new ServerRequest('GET', 'http://example.com/'))
            ->withCookieParams(['MYSESSID' => $id]);

        $probe = $this->probe();
        $response = $middleware->process($request, $probe);

        $this->assertSame($id, $probe->session->getId());

        $setCookie = $response->getHeaderLine('Set-Cookie');
        $this->assertStringContainsString('MYSESSID=' . $id, $setCookie);
        $this->assertStringNotContainsString('FASTD_SESSID', $setCookie);
    }

    public function testExistingSetCookieHeadersArePreserved(): void
    {
        $probe = $this->probe(fn (): ResponseInterface => (new Text())->withAddedHeader('Set-Cookie', 'foo=bar'));
        $response = $this->middleware->process(new ServerRequest('GET', 'http://example.com/'), $probe);

        $cookies = $response->getHeader('Set-Cookie');
        $this->assertContains('foo=bar', $cookies);
        $this->assertCount(2, $cookies);
    }

    public function testSessionStartedByOuterMiddlewareIsReused(): void
    {
        $session = Session::start($this->handler, str_repeat('c', 32));
        $request = (new ServerRequest('GET', 'http://example.com/'))
            ->withAttribute(SessionMiddleware::ATTRIBUTE, $session);

        $probe = $this->probe();
        $response = $this->middleware->process($request, $probe);

        $this->assertSame($session, $probe->session);
        $this->assertSame(0, $this->handler->writeCalls);
        $this->assertSame([], $response->getHeader('Set-Cookie'));
    }

    public function testSaveFailureIsReportedThroughErrorCallback(): void
    {
        $failing = new class implements SessionHandlerInterface {
            public function read(string $sessionId): array
            {
                return [];
            }

            public function write(string $sessionId, array $data): bool
            {
                return false;
            }

            public function destroy(string $sessionId): bool
            {
                return false;
            }

            public function exists(string $sessionId): bool
            {
                return false;
            }
        };

        $errors = [];
        $middleware = new SessionMiddleware(
            $failing,
            onError: function (string $message) use (&$errors): void {
                $errors[] = $message;
            }
        );

        $probe = $this->probe();
        $response = $middleware->process(new ServerRequest('GET', 'http://example.com/'), $probe);

        $this->assertSame(['session save failed'], $errors);
        $this->assertStringContainsString(
            'FASTD_SESSID=' . $probe->session->getId(),
            $response->getHeaderLine('Set-Cookie')
        );
    }

    public function testRegeneratedIdIsEmittedAndOldCookieIsDestroyed(): void
    {
        $this->handler->store[self::EXISTING_ID] = ['user' => 'jan'];

        $probe = $this->probe(function (SessionInterface $session): void {
            $session->regenerateId();
        });
        $response = $this->middleware->process($this->requestWithCookie(self::EXISTING_ID), $probe);

        $newId = $probe->session->getId();

        $this->assertNotSame(self::EXISTING_ID, $newId);
        $this->assertArrayNotHasKey(self::EXISTING_ID, $this->handler->store);
        $this->assertStringContainsString('FASTD_SESSID=' . $newId, $response->getHeaderLine('Set-Cookie'));
    }

    private function requestWithCookie(string $sessionId): ServerRequest
    {
        return (new ServerRequest('GET', 'http://example.com/'))
            ->withCookieParams(['FASTD_SESSID' => $sessionId]);
    }

    private function probe(?callable $callback = null): object
    {
        return new class($callback) implements RequestHandlerInterface {
            public ?SessionInterface $session = null;

            public function __construct(private $callback)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $session = $request->getAttribute(SessionMiddleware::ATTRIBUTE);
                $this->session = $session instanceof SessionInterface ? $session : null;

                $response = $this->callback === null ? null : ($this->callback)($session);

                return $response instanceof ResponseInterface ? $response : new Text();
            }
        };
    }
}
