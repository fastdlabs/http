<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

require_once __DIR__ . '/ArraySessionHandler.php';

use FastD\Http\Session\Session;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Session 单元测试：数据包、闪存、CSRF、失效销毁与持久化。
 */
class SessionTest extends TestCase
{
    private ArraySessionHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ArraySessionHandler();
    }

    public function testNewSessionHasGeneratedIdAndIsStarted(): void
    {
        $session = Session::start($this->handler, Session::generateId());

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $session->getId());
        $this->assertTrue($session->isStarted());
        $this->assertSame([], $session->all());
        $this->assertCount(0, $session);
    }

    public function testDataBagSupportsArrayAccessCountAndIteration(): void
    {
        $session = Session::start($this->handler, Session::generateId());

        $session['user'] = 'jan';
        $session->set('role', 'admin');

        $this->assertTrue(isset($session['user']));
        $this->assertSame('jan', $session['user']);
        $this->assertTrue($session->has('role'));
        $this->assertCount(2, $session);
        $this->assertEqualsCanonicalizing(['user' => 'jan', 'role' => 'admin'], iterator_to_array($session));

        unset($session['user']);
        $this->assertFalse($session->has('user'));
    }

    public function testGetReturnsDefaultForMissingKeysAndStoresNullValues(): void
    {
        $session = Session::start($this->handler, Session::generateId());

        $this->assertSame('fallback', $session->get('missing', 'fallback'));
        $this->assertNull($session->get('missing'));

        $session->set('nullable', null);
        $this->assertTrue($session->has('nullable'));
        $this->assertNull($session->get('nullable', 'fallback'));
    }

    public function testSavePersistsThroughHandlerAndNextSessionReadsBack(): void
    {
        $id = Session::generateId();
        $session = Session::start($this->handler, $id);
        $session->set('user', 'jan');
        $session->save();

        $this->assertSame(1, $this->handler->writeCalls);
        $this->assertSame(['user' => 'jan'], $this->handler->read($id));

        $next = Session::start($this->handler, $id);
        $this->assertSame('jan', $next->get('user'));
    }

    public function testFlashIsNotVisibleInTheSameRequest(): void
    {
        $session = Session::start($this->handler, Session::generateId());
        $session->flash('notice', 'Saved');

        $this->assertNull($session->getFlash('notice'));
    }

    public function testFlashIsDeliveredOnTheNextRequestAndConsumedOnce(): void
    {
        $id = Session::generateId();
        $first = Session::start($this->handler, $id);
        $first->flash('notice', 'Saved');
        $first->save();

        $second = Session::start($this->handler, $id);
        $this->assertSame('Saved', $second->getFlash('notice'));
        $this->assertNull($second->getFlash('notice'));
        $second->save();

        $third = Session::start($this->handler, $id);
        $this->assertNull($third->getFlash('notice'));
    }

    public function testUnreadFlashExpiresAfterOneRequest(): void
    {
        $id = Session::generateId();
        $first = Session::start($this->handler, $id);
        $first->flash('notice', 'Saved');
        $first->save();

        $second = Session::start($this->handler, $id);
        $second->save();

        $third = Session::start($this->handler, $id);
        $this->assertNull($third->getFlash('notice'));
    }

    public function testInvalidateClearsDataCallsDestroyAndStopsSession(): void
    {
        $id = Session::generateId();
        $session = Session::start($this->handler, $id);
        $session->set('user', 'jan');
        $session->save();

        $session->invalidate();

        $this->assertFalse($session->isStarted());
        $this->assertSame([], $session->all());
        $this->assertSame(1, $this->handler->destroyCalls);
        $this->assertFalse($this->handler->exists($id));

        $session->save();
        $this->assertSame(1, $this->handler->writeCalls);
    }

    public function testTokenIsGeneratedOnceAndPersistedAcrossRequests(): void
    {
        $id = Session::generateId();
        $session = Session::start($this->handler, $id);

        $token = $session->getToken();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame($token, $session->getToken());

        $session->save();

        $next = Session::start($this->handler, $id);
        $this->assertSame($token, $next->getToken());
    }

    public function testRegenerateTokenReplacesTheToken(): void
    {
        $session = Session::start($this->handler, Session::generateId());

        $original = $session->getToken();
        $regenerated = $session->regenerateToken();

        $this->assertNotSame($original, $regenerated);
        $this->assertSame($regenerated, $session->getToken());
    }

    public function testReservedPayloadKeysAreNotExposedAsUserData(): void
    {
        $id = Session::generateId();
        $this->handler->store[$id] = [
            'user' => 'jan',
            Session::FLASH_KEY => ['notice' => 'Saved'],
            Session::TOKEN_KEY => 'token-value',
        ];

        $session = Session::start($this->handler, $id);

        $this->assertSame(['user' => 'jan'], $session->all());
        $this->assertSame('token-value', $session->getToken());
        $this->assertSame('Saved', $session->getFlash('notice'));
    }

    public function testNonStringKeysAreRejected(): void
    {
        $session = Session::start($this->handler, Session::generateId());

        $this->expectException(InvalidArgumentException::class);
        $session[[]] = 'value';
    }

    public function testSaveReturnsTrueOnSuccessAndFalseWhenSessionIsNoLongerStarted(): void
    {
        $session = Session::start($this->handler, Session::generateId());
        $this->assertTrue($session->save());

        $session->invalidate();
        $this->assertFalse($session->save());
    }

    public function testIsTokenValidUsesTimingSafeComparisonAndTracksRegeneration(): void
    {
        $session = Session::start($this->handler, Session::generateId());
        $token = $session->getToken();

        $this->assertTrue($session->isTokenValid($token));
        $this->assertFalse($session->isTokenValid('invalid'));

        $regenerated = $session->regenerateToken();
        $this->assertFalse($session->isTokenValid($token));
        $this->assertTrue($session->isTokenValid($regenerated));
    }

    public function testRegenerateIdRotatesStorageKeyAndKeepsData(): void
    {
        $id = Session::generateId();
        $session = Session::start($this->handler, $id);
        $session->set('user', 'jan');
        $session->save();

        $newId = $session->regenerateId();

        $this->assertNotSame($id, $newId);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $newId);
        $this->assertFalse($this->handler->exists($id));
        $this->assertSame(1, $this->handler->destroyCalls);

        $this->assertTrue($session->save());
        $this->assertSame(['user' => 'jan'], $this->handler->read($newId));
    }
}
