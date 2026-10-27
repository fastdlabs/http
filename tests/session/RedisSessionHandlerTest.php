<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Session\Handler\RedisClientInterface;
use FastD\Http\Session\Handler\RedisSessionHandler;
use PHPUnit\Framework\TestCase;

final class FakeRedisClient implements RedisClientInterface
{
    /** @var array<string, array{value: string, expires: int}> */
    public array $rows = [];

    public function get(string $key): string|false
    {
        $row = $this->rows[$key] ?? null;
        if ($row === null || $row['expires'] <= time()) {
            unset($this->rows[$key]);

            return false;
        }

        return $row['value'];
    }

    public function setex(string $key, int $ttl, string $value): bool
    {
        $this->rows[$key] = ['value' => $value, 'expires' => time() + $ttl];

        return true;
    }

    public function del(string $key): int
    {
        $exists = isset($this->rows[$key]);
        unset($this->rows[$key]);

        return $exists ? 1 : 0;
    }

    public function exists(string $key): int
    {
        return $this->get($key) === false ? 0 : 1;
    }
}

final class RedisSessionHandlerTest extends TestCase
{
    public function testWriteReadRoundtrip(): void
    {
        $client = new FakeRedisClient();
        $handler = new RedisSessionHandler($client, 60);

        $this->assertTrue($handler->write('abc', ['user' => 'jan', 'nested' => ['a' => 1]]));
        $this->assertTrue($handler->exists('abc'));
        $this->assertSame(['user' => 'jan', 'nested' => ['a' => 1]], $handler->read('abc'));
        $this->assertArrayHasKey('fastd:session:abc', $client->rows);
    }

    public function testDestroyRemovesSession(): void
    {
        $handler = new RedisSessionHandler(new FakeRedisClient(), 60);
        $handler->write('abc', ['user' => 'jan']);

        $this->assertTrue($handler->destroy('abc'));
        $this->assertFalse($handler->exists('abc'));
        $this->assertFalse($handler->destroy('abc'));
    }

    public function testInvalidJsonReturnsEmptyArray(): void
    {
        $client = new FakeRedisClient();
        $client->setex('fastd:session:bad', 60, '{invalid');

        $this->assertSame([], (new RedisSessionHandler($client))->read('bad'));
    }

    public function testOversizedPayloadIsRejected(): void
    {
        $handler = new RedisSessionHandler(new FakeRedisClient(), 60, 'fastd:session:', 32);

        $this->assertFalse($handler->write('abc', ['blob' => str_repeat('x', 64)]));
        $this->assertFalse($handler->exists('abc'));
    }

    public function testGcIsNoopBecauseRedisExpiresKeys(): void
    {
        $this->assertSame(0, (new RedisSessionHandler(new FakeRedisClient()))->gc());
    }
}
