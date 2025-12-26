<?php

declare(strict_types=1);

use FastD\Http\Request\Request;
use FastD\Http\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use FastD\Http\Stream\Stream;

class RequestTest extends TestCase
{
    /**
     * 测试构造函数初始化
     */
    public function testConstructorInitialization()
    {
        $method = 'GET';
        $uri = 'http://example.com/test';
        $stream = new \FastD\Http\Stream\Stream("php://temp", "rw");
        $stream->write('test body');

        $request = new Request($method, $uri, $stream);

        // 验证方法
        $this->assertSame(strtoupper($method), $request->getMethod());
        // 验证URI
        $this->assertInstanceOf(UriInterface::class, $request->getUri());
        $this->assertSame($uri, (string)$request->getUri());
        // 验证流
        $this->assertSame('test body', (string)$request->getBody());
    }

    /**
     * 测试默认流初始化
     */
    public function testDefaultStreamInitialization()
    {
        $request = new Request('GET', 'http://example.com');

        $this->assertInstanceOf(Stream::class, $request->getBody());
        $this->assertSame('', (string)$request->getBody());
    }

    /**
     * 测试获取请求目标（request target）
     */
    public function testGetRequestTarget()
    {
        // 带路径的URI
        $uri = new Uri('http://example.com/path?query=1');
        $request = (new Request('GET', ''))->withUri($uri);
        $this->assertSame('/path', $request->getRequestTarget());

        // 空路径的URI
        $uri = new Uri('http://example.com');
        $request = (new Request('GET', ''))->withUri($uri);
        $this->assertSame('/', $request->getRequestTarget());

        // 根路径的URI
        $uri = new Uri('http://example.com/');
        $request = (new Request('GET', ''))->withUri($uri);
        $this->assertSame('/', $request->getRequestTarget());
    }

    /**
     * 测试设置请求目标
     */
    public function testWithRequestTarget()
    {
        $request = new Request('GET', 'http://example.com');
        $newRequest = $request->withRequestTarget('/new-target');

        $this->assertSame($request, $newRequest); // 验证返回自身（当前实现）
        $this->assertSame('/new-target', $request->getRequestTarget());
    }

    /**
     * 测试获取HTTP方法
     */
    public function testGetMethod()
    {
        $request = new Request('POST', 'http://example.com');
        $this->assertSame('POST', $request->getMethod());

        $request = new Request('put', 'http://example.com'); // 小写方法
        $this->assertSame('PUT', $request->getMethod()); // 验证自动转为大写
    }

    /**
     * 测试设置有效的HTTP方法
     */
    public function testWithValidMethod()
    {
        $request = new Request('GET', 'http://example.com');

        $newRequest = $request->withMethod('POST');
        $this->assertSame($request, $newRequest); // 验证返回自身（当前实现）
        $this->assertSame('POST', $request->getMethod());

        $newRequest = $request->withMethod('patch'); // 小写方法
        $this->assertSame('PATCH', $request->getMethod());
    }

    /**
     * 测试设置无效的HTTP方法（应抛出异常）
     */
    public function testWithInvalidMethodThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported HTTP method "INVALID" provided');

        $request = new Request('GET', 'http://example.com');
        $request->withMethod('INVALID');
    }

    /**
     * 测试获取URI
     */
    public function testGetUri()
    {
        $uri = new Uri('http://example.com/test');
        $request = (new Request('GET', ''))->withUri($uri);

        $this->assertSame($uri, $request->getUri());
        $this->assertSame('http://example.com/test', (string)$request->getUri());
    }

    /**
     * 测试设置URI
     */
    public function testWithUri()
    {
        $originalUri = new Uri('http://example.com/original');
        $request = (new Request('GET', ''))->withUri($originalUri);

        $newUri = new Uri('http://example.com/new');
        $newRequest = $request->withUri($newUri);

        $this->assertSame($request, $newRequest); // 验证返回自身（当前实现）
        $this->assertSame($newUri, $request->getUri());
        $this->assertSame('http://example.com/new', (string)$request->getUri());
    }

    /**
     * 测试URI设置时的Host头处理（当前实现未处理，仅做基础验证）
     */
    public function testWithUriHostHeader()
    {
        $uri = new Uri('http://example.com');
        $request = (new Request('GET', ''))->withUri($uri);

        // 当前实现未处理Host头，验证默认行为
        $this->assertFalse($request->hasHeader('Host'));

        // 设置带端口的URI
        $newUri = new Uri('http://example.com:8080/path');
        $request->withUri($newUri);
        $this->assertFalse($request->hasHeader('Host')); // 仍未处理
    }
}
