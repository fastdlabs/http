<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Session\Handler\SwooleTableSessionHandler;
use PHPUnit\Framework\TestCase;

/**
 * SwooleTableSessionHandler 单元测试：共享内存读写与 TTL 过期。
 */
class SwooleTableSessionHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('swoole')) {
            $this->markTestSkipped('ext-swoole is required to run SwooleTableSessionHandler tests.');
        }
    }

    public function testWriteReadRoundtrip(): void
    {
        $handler = new SwooleTableSessionHandler(60, 64, 1024);
        $data = ['user' => 'jan', 'nested' => ['a' => 1]];

        $this->assertTrue($handler->write('abc123', $data));
        $this->assertTrue($handler->exists('abc123'));
        $this->assertSame($data, $handler->read('abc123'));
    }

    public function testExpiredSessionIsNotReturnedAndRowIsRemoved(): void
    {
        $handler = new SwooleTableSessionHandler(0, 64, 1024);
        $handler->write('expired', ['user' => 'jan']);

        $this->assertFalse($handler->exists('expired'));
        $this->assertSame([], $handler->read('expired'));
    }

    public function testDestroyRemovesTheSession(): void
    {
        $handler = new SwooleTableSessionHandler(60, 64, 1024);
        $handler->write('abc123', ['user' => 'jan']);

        $this->assertTrue($handler->destroy('abc123'));
        $this->assertFalse($handler->exists('abc123'));
        $this->assertFalse($handler->destroy('abc123'));
    }

    public function testPayloadExceedingColumnSizeIsRejected(): void
    {
        $handler = new SwooleTableSessionHandler(60, 64, 32);

        $this->assertFalse($handler->write('abc123', ['blob' => str_repeat('x', 256)]));
        $this->assertFalse($handler->exists('abc123'));
    }
}
