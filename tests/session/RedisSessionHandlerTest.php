<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Session\Handler\RedisSessionHandler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Redis;

#[AllowMockObjectsWithoutExpectations]
final class RedisSessionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('ext-redis is required.');
        }
    }

    public function testConstructorRequiresRedisObject(): void
    {
        $parameter = (new \ReflectionMethod(RedisSessionHandler::class, '__construct'))->getParameters()[0];

        $this->assertInstanceOf(\ReflectionNamedType::class, $parameter->getType());
        $this->assertSame(Redis::class, $parameter->getType()->getName());
    }

    public function testReadDecodesJson(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('get')
            ->with('fastd:session:abc')
            ->willReturn(json_encode(['user' => 'jan']));

        $this->assertSame(['user' => 'jan'], (new RedisSessionHandler($redis))->read('abc'));
    }

    public function testWriteUsesSetexWithTtlAndJson(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('setex')
            ->with('fastd:session:abc', 60, json_encode(['user' => 'jan']))
            ->willReturn(true);

        $handler = new RedisSessionHandler($redis, 60);

        $this->assertTrue($handler->write('abc', ['user' => 'jan']));
    }

    public function testDestroyAndExistsUsePrefixedKey(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())->method('del')->with('fastd:session:abc')->willReturn(1);
        $redis->expects($this->once())->method('exists')->with('fastd:session:abc')->willReturn(1);

        $handler = new RedisSessionHandler($redis);

        $this->assertTrue($handler->destroy('abc'));
        $this->assertTrue($handler->exists('abc'));
    }

    public function testInvalidJsonReturnsEmptyArray(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->method('get')->willReturn('{invalid');

        $this->assertSame([], (new RedisSessionHandler($redis))->read('bad'));
    }

    public function testOversizedPayloadIsRejectedBeforeRedisWrite(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->never())->method('setex');

        $handler = new RedisSessionHandler($redis, 60, 'fastd:session:', 32);

        $this->assertFalse($handler->write('abc', ['blob' => str_repeat('x', 64)]));
    }

    public function testGcIsNoopBecauseRedisExpiresKeys(): void
    {
        $this->assertSame(0, (new RedisSessionHandler($this->createMock(Redis::class)))->gc());
    }
}
