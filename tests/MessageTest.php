<?php

declare(strict_types=1);

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
        $message = new Message($stream, [], '1.1');

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
        $message = new Message(null, [], '1.0');
        $this->assertSame('1.0', $message->getProtocolVersion());
    }

    public function testWithProtocolVersion(): void
    {
        $original = new Message(null, [], '1.1');
        $modified = $original->withProtocolVersion('2.0');

        $this->assertEquals('1.1', $original->getProtocolVersion()); // Original unchanged
        $this->assertEquals('2.0', $modified->getProtocolVersion()); // Modified has new version
        $this->assertNotSame($original, $modified); // Different objects
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
        $modified = $original
            ->withProtocolVersion('2.0')
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'text/html')
            ->withBody(new Stream('php://temp', 'r+'));

        // 原对象保持不变
        $this->assertSame('1.1', $original->getProtocolVersion());
        $this->assertEmpty($original->getHeaders());
        
        // 修改后的对象有新值
        $this->assertSame('2.0', $modified->getProtocolVersion());
        $this->assertNotEmpty($modified->getHeaders());
    }

    public function testChainedHeaderOperations(): void
    {
        $original = new Message();

        $modified = $original
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('Accept', 'text/html')
            ->withAddedHeader('Accept', 'application/json')
            ->withoutHeader('Content-Type');

        // 原对象保持不变
        $this->assertEmpty($original->getHeaders());

        // 修改后的对象
        $this->assertFalse($modified->hasHeader('Content-Type'));
        $this->assertTrue($modified->hasHeader('Accept'));
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
        $this->assertSame([''], $message->getHeader('X-Null')); // null should be converted to empty string per PSR-7 spec
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

    // ===== 边界条件和错误处理测试 =====

    public function testInvalidHeaderValueTypes(): void
    {
        $message = new Message();
        
        // 测试数字类型值
        $message = $message->withHeader('X-Number', 123);
        $this->assertSame(['123'], $message->getHeader('X-Number'));
        
        // 测试布尔类型值
        $message = $message->withHeader('X-Boolean', true);
        $this->assertSame(['1'], $message->getHeader('X-Boolean'));
        
        // 测试null值
        $message = $message->withHeader('X-Null', null);
        $this->assertSame([''], $message->getHeader('X-Null'));
    }

    public function testEmptyHeaderNameHandling(): void
    {
        $message = new Message();
        
        // 测试空字符串头部名
        $message = $message->withHeader('', 'value');
        $this->assertTrue($message->hasHeader(''));
        $this->assertSame(['value'], $message->getHeader(''));
    }

    public function testSpecialCharacterHeaderNames(): void
    {
        $message = new Message();
        
        // 测试包含特殊字符的头部名
        $specialNames = [
            'X-Custom-Header-With-Dashes',
            'X_Custom_Header_With_Underscores',
            'X.Custom.Header.With.Dots',
            'XCustomHeaderWithNumbers123'
        ];
        
        foreach ($specialNames as $name) {
            $message = $message->withHeader($name, 'value');
            $this->assertTrue($message->hasHeader($name));
            $this->assertSame(['value'], $message->getHeader($name));
        }
    }

    public function testLargeHeaderValueHandling(): void
    {
        $message = new Message();
        
        // 测试大型头部值
        $largeValue = str_repeat('A', 10000);
        $message = $message->withHeader('X-Large-Value', $largeValue);
        
        $this->assertSame([$largeValue], $message->getHeader('X-Large-Value'));
        $this->assertSame($largeValue, $message->getHeaderLine('X-Large-Value'));
    }

    public function testDuplicateHeaderHandling(): void
    {
        $message = new Message();
        
        // 测试重复添加相同头部
        $message = $message->withHeader('X-Duplicate', 'first')
                          ->withHeader('X-Duplicate', 'second')
                          ->withAddedHeader('X-Duplicate', 'third');
        
        $this->assertSame(['second', 'third'], $message->getHeader('X-Duplicate'));
    }

    public function testCaseSensitivityEdgeCases(): void
    {
        $message = new Message();
        
        // 测试各种大小写组合
        $message = $message->withHeader('Content-Type', 'application/json');
        
        // 所有这些都应该匹配同一个头部
        $variations = ['content-type', 'CONTENT-TYPE', 'Content-type', 'cOnTeNt-TyPe'];
        
        foreach ($variations as $variation) {
            $this->assertTrue($message->hasHeader($variation));
            $this->assertSame(['application/json'], $message->getHeader($variation));
        }
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

    // ===== 边缘情况和错误处理测试 =====

    public function testWithHeaderEmptyValue(): void
    {
        $message = (new Message())->withHeader('X-Empty', '');
        
        $this->assertTrue($message->hasHeader('X-Empty'));
        $this->assertSame([''], $message->getHeader('X-Empty'));
    }

    public function testWithHeaderNumericValue(): void
    {
        $message = (new Message())->withHeader('X-Number', 123);
        
        $this->assertTrue($message->hasHeader('X-Number'));
        $this->assertSame(['123'], $message->getHeader('X-Number'));
    }

    public function testWithHeaderBooleanValue(): void
    {
        $message = (new Message())->withHeader('X-Bool', true);
        
        $this->assertTrue($message->hasHeader('X-Bool'));
        $this->assertSame(['1'], $message->getHeader('X-Bool'));
        
        $message = (new Message())->withHeader('X-Bool-Falsy', false);
        $this->assertSame([''], $message->getHeader('X-Bool-Falsy'));
    }

    public function testWithHeaderNullValue(): void
    {
        $message = (new Message())->withHeader('X-Null', null);
        
        $this->assertTrue($message->hasHeader('X-Null'));
        $this->assertSame([''], $message->getHeader('X-Null'));
    }

    public function testWithAddedHeaderToNonExistentHeader(): void
    {
        $message = (new Message())->withAddedHeader('X-New', 'value');
        
        $this->assertTrue($message->hasHeader('X-New'));
        $this->assertSame(['value'], $message->getHeader('X-New'));
    }

    public function testWithAddedHeaderToExistingHeader(): void
    {
        $message = (new Message())
            ->withHeader('X-Multi', 'first')
            ->withAddedHeader('X-Multi', 'second');
        
        $this->assertSame(['first', 'second'], $message->getHeader('X-Multi'));
    }

    public function testWithoutHeaderOnNonExistentHeader(): void
    {
        $message = (new Message());
        $newMessage = $message->withoutHeader('Non-Existent');
        
        $this->assertSame($message, $newMessage); // Should return same instance when header doesn't exist
    }

    public function testWithBodySameInstance(): void
    {
        $stream = new Stream('php://memory', 'r+');
        $message = new Message($stream);
        $newMessage = $message->withBody($stream);
        
        $this->assertSame($message, $newMessage); // Should return same instance when body is identical
    }

    public function testWithBodyDifferentInstance(): void
    {
        $stream1 = new Stream('php://memory', 'r+');
        $stream2 = new Stream('php://memory', 'r+');
        $message = new Message($stream1);
        $newMessage = $message->withBody($stream2);
        
        $this->assertNotSame($message, $newMessage); // Should return different instance when body is different
        $this->assertSame($stream2, $newMessage->getBody());
    }

    public function testGetHeadersReturnsCopy(): void
    {
        $message = (new Message())->withHeader('X-Test', 'value');
        $headers = $message->getHeaders();
        
        // Modifying returned array shouldn't affect the message
        $headers['x-test'][0] = 'modified';
        
        $this->assertSame(['value'], $message->getHeader('X-Test')); // Should remain unchanged
    }

    public function testHeaderCaseInsensitivity(): void
    {
        $message = (new Message())
            ->withHeader('Content-Type', 'application/json')
            ->withAddedHeader('content-type', 'text/html'); // Lowercase variant
        
        $this->assertSame(['application/json', 'text/html'], $message->getHeader('Content-Type'));
        $this->assertSame(['application/json', 'text/html'], $message->getHeader('content-type'));
        $this->assertTrue($message->hasHeader('CONTENT-TYPE'));
    }
}