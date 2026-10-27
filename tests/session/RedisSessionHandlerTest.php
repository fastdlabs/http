<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Session\Handler\RedisSessionHandler;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function testReadReturnsEmptyArrayForNonStringRedisValues(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->exactly(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(false, null, 123);

        $handler = new RedisSessionHandler($redis);

        $this->assertSame([], $handler->read('a'));
        $this->assertSame([], $handler->read('b'));
        $this->assertSame([], $handler->read('c'));
    }

    #[DataProvider('nonArrayJsonProvider')]
    public function testReadReturnsEmptyArrayForScalarJson(string $payload): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->method('get')->willReturn($payload);

        $this->assertSame([], (new RedisSessionHandler($redis))->read('scalar'));
    }

    public static function nonArrayJsonProvider(): array
    {
        return [
            'null' => ['null'],
            'integer' => ['123'],
            'string' => ['"value"'],
            'boolean' => ['true'],
        ];
    }

    public function testReadUsesCustomPrefix(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('get')
            ->with('custom:abc')
            ->willReturn(json_encode(['ok' => true]));

        $this->assertSame(['ok' => true], (new RedisSessionHandler($redis, 60, 'custom:'))->read('abc'));
    }

    public function testWriteEncodesUnicodeWithoutEscaping(): void
    {
        $data = ['name' => '简'];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('setex')
            ->with('fastd:session:abc', 60, $json)
            ->willReturn(true);

        $this->assertTrue((new RedisSessionHandler($redis, 60))->write('abc', $data));
    }

    public function testWriteAllowsPayloadAtExactMaxDataSize(): void
    {
        $data = ['blob' => str_repeat('x', 32)];
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('setex')
            ->with('fastd:session:abc', 60, $json)
            ->willReturn(true);

        $handler = new RedisSessionHandler($redis, 60, 'fastd:session:', strlen($json));

        $this->assertTrue($handler->write('abc', $data));
    }

    public function testWriteReturnsFalseWhenJsonEncodingFails(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->never())->method('setex');

        $handler = new RedisSessionHandler($redis, 60);

        $this->assertFalse($handler->write('abc', ['invalid' => "\xB1\x31"]));
    }

    public function testWriteReturnsFalseWhenSetexFails(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())
            ->method('setex')
            ->willReturn(false);

        $this->assertFalse((new RedisSessionHandler($redis, 60))->write('abc', ['ok' => true]));
    }

    public function testDestroyReturnsFalseWhenKeyDoesNotExist(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())->method('del')->with('fastd:session:missing')->willReturn(0);

        $this->assertFalse((new RedisSessionHandler($redis))->destroy('missing'));
    }

    public function testExistsReturnsFalseWhenKeyDoesNotExist(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->once())->method('exists')->with('fastd:session:missing')->willReturn(0);

        $this->assertFalse((new RedisSessionHandler($redis))->exists('missing'));
    }

    public function testGcAcceptsMaxLifetimeAndRemainsNoop(): void
    {
        $redis = $this->createMock(Redis::class);
        $redis->expects($this->never())->method('get');
        $redis->expects($this->never())->method('setex');
        $redis->expects($this->never())->method('del');

        $this->assertSame(0, (new RedisSessionHandler($redis))->gc(3600));
    }
}
