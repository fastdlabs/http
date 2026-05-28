<?php

declare(strict_types=1);

use FastD\Http\Response\StatusCode;
use FastD\Http\Response\Text;
use PHPUnit\Framework\TestCase;

/**
 * Text响应类完整单元测试
 */
class TextTest extends TestCase
{
    // ===== 基础功能测试 =====

    public function testResponseText()
    {
        $response = new Text(200, 'Hello World');
        $this->assertEquals('Hello World', $response->getContents());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    // ===== 构造函数测试 =====

    public function testConstructorWithDefaultParameters()
    {
        $response = new Text();
        
        $this->assertEquals('', $response->getContents());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function testConstructorWithCustomContent()
    {
        $response = new Text(200, 'Custom content');
        
        $this->assertEquals('Custom content', $response->getContents());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConstructorWithCustomStatus()
    {
        $response = new Text(StatusCode::HTTP_CREATED, 'Content');
        
        $this->assertEquals('Content', $response->getContents());
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getReasonPhrase());
    }

    public function testConstructorWithHeaders()
    {
        $headers = [
            'X-Custom-Header' => ['custom-value'],
            'Cache-Control' => ['no-cache']
        ];
        
        $response = new Text(200, 'Content', $headers);
        
        $this->assertTrue($response->hasHeader('X-Custom-Header'));
        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertEquals('custom-value', $response->getHeaderLine('X-Custom-Header'));
        $this->assertEquals('no-cache', $response->getHeaderLine('Cache-Control'));
    }

    // ===== 内容操作测试 =====

    public function testWithContents()
    {
        $response = new Text();
        $response = $response->withContents('New content');
        
        $this->assertEquals('New content', $response->getContents());
    }

    public function testGetContents()
    {
        $response = new Text(200, 'Test content');
        
        $this->assertEquals('Test content', $response->getContents());
    }

    // ===== 状态码和原因短语测试 =====

    public function testWithStatus()
    {
        $response = (new Text(200, 'Content'))->withStatus(404);
        
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getReasonPhrase());
    }

    public function testWithStatusAndCustomReasonPhrase()
    {
        $response = (new Text(200, 'Content'))->withStatus(201, 'Custom Created');
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Custom Created', $response->getReasonPhrase());
    }

    public function testGetReasonPhrase()
    {
        $response = new Text(StatusCode::HTTP_ACCEPTED, 'Content');
        
        $this->assertEquals('Accepted', $response->getReasonPhrase());
    }

    public function testInvalidStatusCode()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $response = (new Text(200, 'Content'))->withStatus(999);
    }

    // ===== 状态判断测试 =====
    // 注意：Text类不提供isOk、isSuccessful等便捷方法，需要直接检查状态码

    public function testStatusCodeChecking()
    {
        // 测试200 OK
        $response = new Text(200, 'Content');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        
        // 测试404 Not Found
        $response = new Text(404, 'Content');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found', $response->getReasonPhrase());
        
        // 测试500 Internal Server Error
        $response = new Text(500, 'Content');
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());
    }

    // ===== 内容类型测试 =====

    public function testWithContentType()
    {
        $original = new Text(200, 'Content');
        $response = $original->withContentType('text/html');
        
        $this->assertEquals('text/html', $response->getContentType());
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testGetContentType()
    {
        $response = (new Text(200, 'Content'))->withHeader('Content-Type', 'application/json');
        
        $this->assertEquals('application/json', $response->getContentType());
    }

    // ===== 缓存控制测试 =====

    public function testWithCacheControl()
    {
        $original = new Text(200, 'Content');
        $response = $original->withCacheControl('no-cache');
        
        $this->assertEquals('no-cache', $response->getCacheControl());
        $this->assertEquals('no-cache', $response->getHeaderLine('Cache-Control'));
    }

    public function testGetCacheControl()
    {
        $response = (new Text(200, 'Content'))->withHeader('Cache-Control', 'max-age=3600');
        
        $this->assertEquals('max-age=3600', $response->getCacheControl());
    }

    // ===== ETag测试 =====

    public function testWithETag()
    {
        $original = new Text(200, 'Content');
        $response = $original->withETag('abc123');
        
        $this->assertEquals('abc123', $response->getETag());
        $this->assertEquals('abc123', $response->getHeaderLine('ETag'));
    }

    public function testWithWeakETag()
    {
        $original = new Text(200, 'Content');
        $response = $original->withETag('abc123', true);
        
        $this->assertEquals('W/abc123', $response->getETag());
        $this->assertEquals('W/abc123', $response->getHeaderLine('ETag'));
    }

    // ===== 过期时间测试 =====

    public function testWithExpires()
    {
        $date = new DateTime('2025-12-31 23:59:59');
        $original = new Text(200, 'Content');
        $response = $original->withExpires($date);
        
        $this->assertNotEmpty($response->getHeaderLine('Expires'));
        $this->assertStringContainsString('31 Dec 2025', $response->getHeaderLine('Expires'));
    }

    public function testGetExpires()
    {
        $date = new DateTime('2025-01-01 12:00:00');
        $response = (new Text(200, 'Content'))->withExpires($date);
        
        $expires = $response->getExpires();
        $this->assertInstanceOf(DateTime::class, $expires);
    }

    // ===== 最大年龄测试 =====

    public function testWithMaxAge()
    {
        $original = new Text(200, 'Content');
        $response = $original->withMaxAge(3600);
        
        $this->assertEquals(3600, $response->getMaxAge());
        $this->assertStringContainsString('max-age=3600', $response->getHeaderLine('Cache-Control'));
    }

    public function testGetMaxAge()
    {
        $response = (new Text(200, 'Content'))->withAddedHeader('Cache-Control', 'max-age=1800');
        
        $this->assertEquals(1800, $response->getMaxAge());
    }

    public function testWithSharedMaxAge()
    {
        $original = new Text(200, 'Content');
        $response = $original->withSharedMaxAge(7200);
        
        $this->assertStringContainsString('s-maxage=7200', $response->getHeaderLine('Cache-Control'));
        $this->assertStringContainsString('public', $response->getHeaderLine('Cache-Control'));
    }

    // ===== 最后修改时间测试 =====

    public function testWithLastModified()
    {
        $date = new DateTime('2024-06-15 10:30:00');
        $original = new Text(200, 'Content');
        $response = $original->withLastModified($date);
        
        $this->assertNotEmpty($response->getLastModified());
        $this->assertStringContainsString('15 Jun 2024', $response->getLastModified());
    }

    public function testGetLastModified()
    {
        $date = new DateTime('2024-01-01 00:00:00');
        $response = (new Text(200, 'Content'))->withLastModified($date);
        
        $this->assertNotEmpty($response->getLastModified());
    }

    // ===== 304响应测试 =====

    public function testWithNotModified()
    {
        $response = (new Text(200, 'Original content'))
            ->withHeader('Content-Type', 'text/plain')
            ->withHeader('Content-Length', '16');
        
        // 检查原始状态
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertTrue($response->hasHeader('Content-Length'));
        
        // 应用withNotModified
        $modifiedResponse = $response->withNotModified();
        
        // 检查修改后的状态
        $this->assertEquals(304, $modifiedResponse->getStatusCode());
        $this->assertEquals('Not Modified', $modifiedResponse->getReasonPhrase());
        $this->assertFalse($modifiedResponse->hasHeader('Content-Type')); // Removed per RFC
        $this->assertFalse($modifiedResponse->hasHeader('Content-Length')); // Removed per RFC
        
        // 验证原始响应未被修改
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertTrue($response->hasHeader('Content-Length'));
    }

    // ===== Cookie测试 =====

    public function testWithCookie()
    {
        $cookie = \FastD\Http\Cookie::create('session_id', 'abc123');
        $response = (new Text(200, 'Content'))->withCookie($cookie);
        
        $cookies = $response->getCookies();
        $this->assertArrayHasKey('session_id', $cookies);
        $cookieString = (string)$cookies['session_id'];
        $this->assertStringStartsWith('session_id=abc123', $cookieString);
    }

    public function testGetCookies()
    {
        $cookie1 = \FastD\Http\Cookie::create('cookie1', 'value1');
        $cookie2 = \FastD\Http\Cookie::create('cookie2', 'value2');
        $response = (new Text(200, 'Content'))
            ->withCookie($cookie1)
            ->withCookie($cookie2);
        
        $cookies = $response->getCookies();
        $this->assertCount(2, $cookies);
        $this->assertArrayHasKey('cookie1', $cookies);
        $this->assertArrayHasKey('cookie2', $cookies);
    }

    // ===== 文件描述符测试 =====
    // 注意：Text类不提供withFileDescriptor和getFileDescriptor方法

    // ===== 头部操作测试 =====

    public function testWithMultipleHeaders()
    {
        $original = new Text(200, 'Content');
        $response = $original
            ->withHeader('X-Test-Header', 'test-value')
            ->withHeader('X-Array-Header', ['value1', 'value2']);
        
        $this->assertEquals('test-value', $response->getHeaderLine('X-Test-Header'));
        $this->assertEquals(['value1', 'value2'], $response->getHeader('X-Array-Header'));
    }

    // ===== toString方法测试 =====

    public function testToStringMethod()
    {
        $response = new Text(201, 'Hello World');
        $response = $response->withHeader('X-Custom', 'value');
        $response = $response->withContentType('text/plain');
        
        $stringRepresentation = (string)$response;
        
        $this->assertStringContainsString('HTTP/1.1 201 Created', $stringRepresentation);
        $this->assertStringContainsString('X-Custom: value', $stringRepresentation);
        $this->assertStringContainsString('Content-Type: text/plain', $stringRepresentation);
        $this->assertStringContainsString('Hello World', $stringRepresentation);
    }

    // ===== 边界情况测试 =====

    public function testEmptyContent()
    {
        $response = new Text(200, '');
        
        $this->assertEquals('', $response->getContents());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLargeContent()
    {
        $largeContent = str_repeat('A', 10000);
        $response = new Text(200, $largeContent);
        
        $this->assertEquals($largeContent, $response->getContents());
        $this->assertEquals(10000, strlen($response->getContents()));
    }

    public function testSpecialCharactersInContent()
    {
        $specialContent = "Line 1\nLine 2\tTabbed Content 中文内容 🌍";
        $response = new Text(200, $specialContent);
        
        $this->assertEquals($specialContent, $response->getContents());
    }

    // ===== 性能测试 =====

    public function testPerformanceWithMultipleResponses()
    {
        $startTime = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            $response = new Text(200, "Content $i", ['X-Index' => $i]);
            $this->assertEquals(200, $response->getStatusCode());
        }
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        // 100次响应创建应该很快完成
        $this->assertLessThan(100, $duration, 'Creating multiple responses should be fast');
    }

    // ===== 与PSR-7兼容性测试 =====

    public function testImplementsResponseInterface()
    {
        $response = new Text(200, 'Content');
        
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertInstanceOf(\Stringable::class, $response);
    }
}