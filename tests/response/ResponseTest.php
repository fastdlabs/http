<?php

declare(strict_types=1);

use FastD\Http\Response\Response;
use FastD\Http\Response\StatusCodeInterface;
use FastD\Http\Cookie;
use FastD\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Response类完整单元测试
 */
class ResponseTest extends TestCase
{
    private Response $response;

    protected function setUp(): void
    {
        $this->response = new Response();
    }

    // ===== 构造函数测试 =====

    public function testConstructorCreatesInstance(): void
    {
        $response = new Response();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(StatusCodeInterface::class, $response);
    }

    public function testConstructorWithParameters(): void
    {
        $content = 'test content';
        $statusCode = 201;
        $headers = ['Content-Type' => 'application/json'];

        $response = new Response($content, $statusCode, $headers);

        $this->assertSame($content, $response->getContents());
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorDefaults(): void
    {
        $response = new Response();
        $this->assertSame('', $response->getContents());
        $this->assertSame(StatusCodeInterface::HTTP_OK, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
    }

    // ===== 状态码相关测试 =====

    public function testGetStatusCode(): void
    {
        $response = new Response('', 404);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testWithStatus(): void
    {
        $newResponse = $this->response->withStatus(403);
        $this->assertSame(403, $newResponse->getStatusCode());
        $this->assertSame('Forbidden', $newResponse->getReasonPhrase());
    }

    public function testWithStatusWithCustomReasonPhrase(): void
    {
        $newResponse = $this->response->withStatus(403, 'Access Denied');
        $this->assertSame(403, $newResponse->getStatusCode());
        $this->assertSame('Access Denied', $newResponse->getReasonPhrase());
    }

    public function testWithStatusInvalidCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status code "99"; must be an integer between 100 and 599, inclusive');
        $this->response->withStatus(99);
    }

    public function testWithStatusValidRange(): void
    {
        // 测试有效范围内的状态码
        $validCodes = [100, 200, 300, 400, 500, 599];

        foreach ($validCodes as $code) {
            $response = new Response();
            $newResponse = $response->withStatus($code);
            $this->assertSame($code, $newResponse->getStatusCode());
        }
    }

    public function testWithStatusInvalidRange(): void
    {
        // 测试无效范围的状态码
        $invalidCodes = [99, 600, 700];

        foreach ($invalidCodes as $code) {
            $this->expectException(InvalidArgumentException::class);
            $this->response->withStatus($code);
        }
    }

    public function testGetReasonPhrase(): void
    {
        $response = new Response('', 201);
        $this->assertSame('Created', $response->getReasonPhrase());
    }

    public function testIsInvalidStatusCode(): void
    {
        $response = new Response();

        $this->assertFalse($response->isInvalidStatusCode());

        $this->expectException(InvalidArgumentException::class);
        $response->withStatus(99);
        $this->assertTrue($response->isInvalidStatusCode());

        $response->withStatus(600);
        $this->assertTrue($response->isInvalidStatusCode());

        $response->withStatus(200);
        $this->assertFalse($response->isInvalidStatusCode());
    }

    // ===== 响应状态判断测试 =====

    public function testIsSuccessful(): void
    {
        $response = new Response();

        $this->assertTrue($response->isSuccessful());

        $response->withStatus(199);
        $this->assertFalse($response->isSuccessful());

        $response->withStatus(300);
        $this->assertFalse($response->isSuccessful());
    }

    public function testIsServerError(): void
    {
        $response = new Response();

        $this->assertFalse($response->isServerError());

        $response->withStatus(500);
        $this->assertTrue($response->isServerError());

        $response->withStatus(404);
        $this->assertFalse($response->isServerError());
    }

    public function testIsOk(): void
    {
        $response = new Response();

        $this->assertTrue($response->isOk());

        $response->withStatus(201);
        $this->assertFalse($response->isOk());
    }

    public function testIsForbidden(): void
    {
        $response = new Response();

        $this->assertFalse($response->isForbidden());

        $response->withStatus(403);
        $this->assertTrue($response->isForbidden());
    }

    public function testIsNotFound(): void
    {
        $response = new Response();

        $this->assertFalse($response->isNotFound());

        $response->withStatus(404);
        $this->assertTrue($response->isNotFound());
    }

    public function testIsRedirection(): void
    {
        $response = new Response();

        $this->assertFalse($response->isRedirection());

        $response->withStatus(301);
        $this->assertTrue($response->isRedirection());

        $response->withStatus(200);
        $this->assertFalse($response->isRedirection());

        $response->withStatus(400);
        $this->assertFalse($response->isRedirection());
    }

    // ===== 内容处理测试 =====

    public function testWithContentAndGetContents(): void
    {
        $content = 'Hello World';
        $newResponse = $this->response->withContents($content);

        $this->assertSame($content, $newResponse->getContents());
    }

    public function testWithContentOverwrites(): void
    {
        $firstContent = 'First Content';
        $response = $this->response->withContents($firstContent);
        $this->assertSame($firstContent, $response->getContents());

        $secondContent = 'Second Content';
        $response->getBody()->rewind();
        $response = $response->withContents($secondContent);
        $this->assertSame($secondContent, $response->getContents());
    }

    // ===== 头处理测试 =====

    public function testWithHeaders(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Custom-Header' => 'custom-value'
        ];

        $newResponse = $this->response->withHeaders($headers);

        $this->assertTrue($newResponse->hasHeader('Content-Type'));
        $this->assertTrue($newResponse->hasHeader('X-Custom-Header'));
        $this->assertSame('application/json', $newResponse->getHeaderLine('Content-Type'));
        $this->assertSame('custom-value', $newResponse->getHeaderLine('X-Custom-Header'));
    }

    public function testWithHeadersWithArrayValues(): void
    {
        $headers = [
            'Set-Cookie' => ['cookie1=value1', 'cookie2=value2']
        ];

        $newResponse = $this->response->withHeaders($headers);

        $this->assertTrue($newResponse->hasHeader('Set-Cookie'));
        $this->assertContains('cookie1=value1', $newResponse->getHeader('Set-Cookie'));
        $this->assertContains('cookie2=value2', $newResponse->getHeader('Set-Cookie'));
    }

    public function testWithContentType(): void
    {
        $newResponse = $this->response->withContentType('application/json');

        $this->assertSame('application/json', $newResponse->getContentType());
        $this->assertSame('application/json', $newResponse->getHeaderLine('Content-Type'));
    }

    public function testWithCacheControl(): void
    {
        $newResponse = $this->response->withCacheControl('no-cache');

        $this->assertSame('no-cache', $newResponse->getCacheControl());
        $this->assertSame('no-cache', $newResponse->getHeaderLine('Cache-Control'));
    }

    // ===== Cookie处理测试 =====

    public function testWithCookie(): void
    {
        $newResponse = $this->response->withCookie('session', 'abc123');

        $cookies = $newResponse->getCookies();
        $this->assertArrayHasKey('session', $cookies);
        $this->assertInstanceOf(Cookie::class, $cookies['session']);
        $this->assertSame('session', $cookies['session']->getName());
        $this->assertSame('abc123', $cookies['session']->getValue());
    }

    public function testGetCookies(): void
    {
        $response = $this->response
            ->withCookie('cookie1', 'value1')
            ->withCookie('cookie2', 'value2');

        $cookies = $response->getCookies();
        $this->assertCount(2, $cookies);
        $this->assertArrayHasKey('cookie1', $cookies);
        $this->assertArrayHasKey('cookie2', $cookies);
    }

    // ===== 缓存相关测试 =====

    public function testWithETag(): void
    {
        $newResponse = $this->response->withETag('abc123');

        $this->assertSame('abc123', $newResponse->getETag());
    }

    public function testWithWeakETag(): void
    {
        $newResponse = $this->response->withETag('abc123', true);

        $this->assertSame('W/abc123', $newResponse->getETag());
    }

    public function testWithExpires(): void
    {
        $date = new DateTime('2023-01-01 12:00:00');
        $newResponse = $this->response->withExpires($date);

        $this->assertStringContainsString('Sun, 01 Jan 2023 12:00:00 GMT', $newResponse->getHeaderLine('Expires'));
    }

    public function testGetExpires(): void
    {
        $date = new DateTime('2023-01-01 12:00:00');
        $newResponse = $this->response->withExpires($date);

        $expires = $newResponse->getExpires();
        $this->assertInstanceOf(DateTime::class, $expires);
    }

    public function testWithMaxAge(): void
    {
        $newResponse = $this->response->withMaxAge(3600);

        $this->assertSame(3600, $newResponse->getMaxAge());
        $this->assertStringContainsString('max-age=3600', $newResponse->getHeaderLine('Cache-Control'));
    }

    public function testWithSharedMaxAge(): void
    {
        $newResponse = $this->response->withSharedMaxAge(7200);

        $this->assertStringContainsString('s-maxage=7200', $newResponse->getHeaderLine('Cache-Control'));
        $this->assertStringContainsString('public', $newResponse->getHeaderLine('Cache-Control'));
    }

    public function testWithLastModified(): void
    {
        $date = new DateTime('2023-01-01 12:00:00');
        $newResponse = $this->response->withLastModified($date);

        $this->assertStringContainsString('Sun, 01 Jan 2023 12:00:00 GMT', $newResponse->getHeaderLine('Last-Modified'));
    }

    public function testGetLastModified(): void
    {
        $date = new DateTime('2023-01-01 12:00:00');
        $newResponse = $this->response->withLastModified($date);

        $this->assertStringContainsString('Sun, 01 Jan 2023 12:00:00 GMT', $newResponse->getLastModified());
    }

    // ===== 特殊响应测试 =====

    public function testWithNotModified(): void
    {
        $response = $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', '100')
            ->withContents('some content')
            ->withNotModified();

        $this->assertSame(StatusCodeInterface::HTTP_NOT_MODIFIED, $response->getStatusCode());
        $this->assertSame('some content', $response->getContents());
        $this->assertFalse($response->hasHeader('Content-Type'));
        $this->assertFalse($response->hasHeader('Content-Length'));
    }

    // ===== 文件描述符测试 =====

    public function testWithFileDescriptor(): void
    {
        $newResponse = $this->response->withFileDescriptor(123);

        $this->assertSame(123, $newResponse->getFileDescriptor());
    }

    public function testGetFileDescriptor(): void
    {
        $this->assertNull($this->response->getFileDescriptor());

        $response = $this->response->withFileDescriptor(456);
        $this->assertSame(456, $response->getFileDescriptor());
    }

    // ===== __toString方法测试 =====

    public function testToString(): void
    {
        $response = new Response('Hello World', 200, ['Content-Type' => 'text/plain']);

        $string = (string)$response;

        $this->assertStringContainsString('HTTP/1.1 200 OK', $string);
        $this->assertStringContainsString('Content-Type: text/plain', $string);
        $this->assertStringContainsString('Hello World', $string);
    }

    public function testToStringWithCookies(): void
    {
        $response = (new Response('Hello World', 200))
            ->withCookie('session', 'abc123');

        $string = (string)$response;

        $this->assertStringContainsString('Set-Cookie: session=abc123', $string);
    }

    // ===== 常用状态码测试 =====

    public function testCommonStatusCodes(): void
    {
        $codes = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];

        foreach ($codes as $code => $phrase) {
            $response = new Response('', $code);
            $this->assertSame($code, $response->getStatusCode(), "Status code $code failed");
            $this->assertSame($phrase, $response->getReasonPhrase(), "Reason phrase for code $code failed");
        }
    }

    // ===== 边界情况测试 =====

    public function testMaxAgeWithNegativeValue(): void
    {
        $newResponse = $this->response->withMaxAge(-100);

        // maxAge should be converted to positive
        $this->assertGreaterThanOrEqual(0, $newResponse->getMaxAge());
    }

    public function testSharedMaxAgeWithNegativeValue(): void
    {
        $newResponse = $this->response->withSharedMaxAge(-50);

        // Should not have negative s-maxage
        $cacheControl = $newResponse->getHeaderLine('Cache-Control');
        $this->assertStringNotContainsString('s-maxage=-50', $cacheControl);
        $this->assertStringContainsString('s-maxage=0', $cacheControl);
    }

    public function testExpiresWithPastDate(): void
    {
        $pastDate = new DateTime('-1 hour');
        $newResponse = $this->response->withExpires($pastDate);

        $this->assertLessThanOrEqual(0, $newResponse->getMaxAge());
    }

    public function testGetMaxAgeWhenNoCacheControl(): void
    {
        $response = new Response();

        // Should return 0 when no max-age or expires is set
        $this->assertGreaterThanOrEqual(0, $response->getMaxAge());
    }

    // ===== 响应发送测试（模拟）=====

    public function testResponseImplementsStringable(): void
    {
        $this->assertInstanceOf(\Stringable::class, $this->response);
    }

    public function testResponseImplementsResponseInterface(): void
    {
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $this->response);
    }

    // ===== 状态码范围测试 =====

    public function testStatusCodeRanges(): void
    {
        // 信息响应 (1xx)
        $response = new Response('', 100);
        $this->assertTrue($response->getStatusCode() >= 100 && $response->getStatusCode() < 200);

        // 成功响应 (2xx)
        $response = new Response('', 200);
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // 重定向 (3xx)
        $response = new Response('', 300);
        $this->assertTrue($response->getStatusCode() >= 300 && $response->getStatusCode() < 400);

        // 客户端错误 (4xx)
        $response = new Response('', 400);
        $this->assertTrue($response->getStatusCode() >= 400 && $response->getStatusCode() < 500);

        // 服务器错误 (5xx)
        $response = new Response('', 500);
        $this->assertTrue($response->getStatusCode() >= 500 && $response->getStatusCode() < 600);
    }

    // ===== 流操作测试 =====

    public function testBodyStreamOperations(): void
    {
        $response = new Response('initial content');
        // 验证初始内容
        $this->assertSame('initial content', $response->getContents());

        // 写入新内容
        $newResponse = $response->withContents('new content');
        $this->assertSame('initial contentnew content', $newResponse->getContents());
    }

    // ===== 复杂头处理测试 =====

    public function testComplexHeaderOperations(): void
    {
        $response = new Response();

        // 添加多个相同名称的头
        $response = $response
            ->withAddedHeader('X-Custom', 'value1')
            ->withAddedHeader('X-Custom', 'value2');

        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertContains('value1', $response->getHeader('X-Custom'));
        $this->assertContains('value2', $response->getHeader('X-Custom'));

        // 获取头行
        $headerLine = $response->getHeaderLine('X-Custom');
        $this->assertStringContainsString('value1', $headerLine);
        $this->assertStringContainsString('value2', $headerLine);
    }

    // ===== 空响应测试 =====

    public function testEmptyResponse(): void
    {
        $response = new Response();

        $this->assertSame('', $response->getContents());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame([], $response->getCookies());
    }

    // ===== 构造函数参数验证测试 =====

    public function testConstructorWithInvalidStatusCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Response('', 999);
    }

    public function testConstructorWithValidStatusCode(): void
    {
        $response = new Response('', 201);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Created', $response->getReasonPhrase());
    }
}