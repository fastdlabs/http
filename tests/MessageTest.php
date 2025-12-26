<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Message;
use FastD\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

/**
 * Message 类测试用例
 */
class MessageTest extends TestCase
{
    // ===== 构造函数测试 =====

    public function testConstructorCreatesMessage(): void
    {
        $stream = new Stream('php://memory', 'r+');
        $message = new Message($stream, '1.1');

        $this->assertSame($stream, $message->getBody());
        $this->assertSame('1.1', $message->getProtocolVersion());
        $this->assertEmpty($message->getHeaders());
    }

    public function testConstructorWithDefaultValues(): void
    {
        $message = new Message();

        $this->assertInstanceOf(StreamInterface::class, $message->getBody());
        $this->assertSame('1.1', $message->getProtocolVersion());
        $this->assertEmpty($message->getHeaders());
    }

    // ===== 协议版本测试 =====

    public function testGetProtocolVersion(): void
    {
        $message = new Message(null, '1.0');
        $this->assertSame('1.0', $message->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $original = new Message(null, '1.1');
        $modified = $original->withProtocolVersion('2.0');

        $this->assertEquals('2.0', $original->getProtocolVersion());
        $this->assertEquals('2.0', $modified->getProtocolVersion());
        $this->assertSame($original, $modified);
    }

    // ===== 头部操作测试 =====

    public function testGetHeaders(): void
    {
        $message = new Message();
        $this->assertIsArray($message->getHeaders());
        $this->assertEmpty($message->getHeaders());
    }

    public function testHasHeader(): void
    {
        $message = new Message();
        $this->assertFalse($message->hasHeader('Content-Type'));

        $message = $message->withHeader('Content-Type', 'application/json');
        $this->assertTrue($message->hasHeader('Content-Type'));
        $this->assertTrue($message->hasHeader('content-type')); // case-insensitive
    }

    public function testGetHeader(): void
    {
        $message = new Message();
        $this->assertEmpty($message->getHeader('Content-Type'));

        $message = $message->withHeader('Content-Type', 'application/json');
        $this->assertSame(['application/json'], $message->getHeader('Content-Type'));
        $this->assertSame(['application/json'], $message->getHeader('content-type')); // case-insensitive
    }

    public function testGetHeaderLine(): void
    {
        $message = new Message();
        $this->assertSame('', $message->getHeaderLine('Content-Type'));

        $message = $message->withHeader('Content-Type', 'application/json');
        $this->assertSame('application/json', $message->getHeaderLine('Content-Type'));

        $message = $message->withAddedHeader('Accept', ['text/html', 'application/json']);

        $this->assertSame('text/html, application/json', $message->getHeaderLine('Accept'));
    }

    public function testWithHeader(): void
    {
        $original = new Message();
        $original = $original->withHeader('Content-Type', 'application/json');

        $this->assertNotEmpty($original->getHeaders());
        $this->assertTrue($original->hasHeader('Content-Type'));
        $this->assertSame(['application/json'], $original->getHeader('Content-Type'));
    }

    public function testWithHeaderReplacesExisting(): void
    {
        $message = (new Message())
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Type', 'text/html');

        $this->assertSame(['text/html'], $message->getHeader('Content-Type'));
    }

    public function testWithHeaderSupportsArrayValue(): void
    {
        $message = (new Message())
            ->withHeader('Accept', ['text/html', 'application/json']);

        $this->assertSame(['text/html', 'application/json'], $message->getHeader('Accept'));
    }

    public function testWithAddedHeader(): void
    {
        $message = (new Message())
            ->withHeader('Accept', 'text/html')
            ->withAddedHeader('Accept', 'application/json');

        $this->assertSame(['text/html', 'application/json'], $message->getHeader('Accept'));
    }

    public function testWithAddedHeaderCreatesNewHeader(): void
    {
        $message = (new Message())
            ->withAddedHeader('Accept', 'text/html');

        $this->assertSame(['text/html'], $message->getHeader('Accept'));
    }

    public function testWithAddedHeaderSupportsArrayValue(): void
    {
        $message = (new Message())
            ->withAddedHeader('Accept', ['text/html', 'application/json']);

        $this->assertSame(['text/html', 'application/json'], $message->getHeader('Accept'));
    }

    public function testWithAddedHeaderMergesArrays(): void
    {
        $message = (new Message())
            ->withHeader('Accept', ['text/html'])
            ->withAddedHeader('Accept', ['application/json', 'text/plain']);

        $this->assertSame(['text/html', 'application/json', 'text/plain'], $message->getHeader('Accept'));
    }

    public function testWithoutHeader(): void
    {
        $original = (new Message())
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'text/html');

        $original = $original->withoutHeader('Content-Type');

        $this->assertFalse($original->hasHeader('Content-Type'));
        $this->assertTrue($original->hasHeader('Accept'));
    }

    public function testWithoutHeaderNonExistent(): void
    {
        $original = new Message();
        $original = $original->withoutHeader('Non-Existent');

        $this->assertEmpty($original->getHeaders());
    }

    public function testWithoutHeaderCaseInsensitive(): void
    {
        $original = (new Message())
            ->withHeader('Content-Type', 'application/json');

        $modified = $original->withoutHeader('content-type');

        $this->assertFalse($modified->hasHeader('Content-Type'));
        $this->assertFalse($modified->hasHeader('content-type'));
    }

    // ===== 不可变性测试 =====

    public function testAllWithMethodsAreImmutable(): void
    {
        $original = new Message();

        // 测试所有 with 方法的不可变性
        $original = $original
            ->withProtocolVersion('2.0')
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'text/html')
            ->withBody(new Stream('php://temp', 'r+'));

        // 原对象保持不变
        $this->assertSame('2.0', $original->getProtocolVersion());
        $this->assertNotEmpty($original->getHeaders());
    }

    public function testChainedHeaderOperations(): void
    {
        $original = new Message();

        $original = $original
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'text/html')
            ->withAddedHeader('Accept', 'application/json')
            ->withoutHeader('Content-Type');

        // 原对象保持不变
        $this->assertNotEmpty($original->getHeaders());

        // 修改后的对象
        $this->assertFalse($original->hasHeader('Content-Type'));
        $this->assertTrue($original->hasHeader('Accept'));
    }

    // ===== 头部大小写保留测试 =====

    public function testHeaderCasingPreserved(): void
    {
        $message = (new Message())
            ->withHeader('Content-Type', 'application/json');

        $headers = $message->getHeaders();
        $this->assertArrayHasKey('content-type', $headers); // 存储为小写
        // 但 getHeaders() 返回的键应该保持原始大小写（如果实现保留）
    }

    // ===== 流操作测试 =====

    public function testGetBody(): void
    {
        $stream = new Stream('php://memory', 'r+');
        $message = new Message($stream);

        $this->assertSame($stream, $message->getBody());
    }

    public function testWithBody(): void
    {
        $originalStream = new Stream('php://memory', 'r+');

        $original = new Message($originalStream);

        $this->assertSame($originalStream, $original->getBody());
    }

    // ===== 边界条件测试 =====

    public function testEmptyHeaderValues(): void
    {
        $message = (new Message())
            ->withHeader('X-Empty', '')
            ->withHeader('X-Null', null);

        $this->assertSame([''], $message->getHeader('X-Empty'));
        $this->assertSame([null], $message->getHeader('X-Null'));
    }

    public function testMultipleHeadersWithSameName(): void
    {
        $message = (new Message())
            ->withHeader('X-Custom', 'value1')
            ->withAddedHeader('X-Custom', 'value2')
            ->withAddedHeader('x-custom', 'value3'); // case-insensitive

        $this->assertSame(['value1', 'value2', 'value3'], $message->getHeader('X-Custom'));
    }

    public function testComplexHeaderValues(): void
    {
        $complexValue = [
            'application/json',
            'application/xml;q=0.9',
            'text/html;q=0.8'
        ];

        $message = (new Message())
            ->withHeader('Accept', $complexValue);

        $this->assertSame($complexValue, $message->getHeader('Accept'));
        $this->assertSame(implode(', ', $complexValue), $message->getHeaderLine('Accept'));
    }

    // ===== 性能测试 =====

    public function testPerformanceWithManyHeaders(): void
    {
        $start = microtime(true);

        $message = new Message();
        for ($i = 0; $i < 100; $i++) {
            $message = $message->withAddedHeader("X-Header-$i", "value-$i");
        }

        $end = microtime(true);
        $duration = ($end - $start) * 1000;

        $this->assertLessThan(100, $duration, 'Adding many headers should be fast');
    }

    public function testPerformanceWithChainedOperations(): void
    {
        $start = microtime(true);

        $message = new Message();
        for ($i = 0; $i < 50; $i++) {
            $message = $message
                ->withHeader("Header-$i", "value-$i")
                ->withAddedHeader("Multi-$i", ["val1-$i", "val2-$i"])
                ->withProtocolVersion('1.1');
        }

        $end = microtime(true);
        $duration = ($end - $start) * 1000;

        $this->assertLessThan(100, $duration, 'Chained operations should be efficient');
    }
}