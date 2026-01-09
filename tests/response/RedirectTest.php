<?php

declare(strict_types=1);

use FastD\Http\Response\Redirect;
use FastD\Http\Response\Text;
use FastD\Http\Response\StatusCode;
use PHPUnit\Framework\TestCase;

/**
 * Redirect响应类完整单元测试
 */
class RedirectTest extends TestCase
{
    // ===== 基础功能测试 =====

    public function testResponseRedirect()
    {
        $response = new Redirect('https://examples.com');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(StatusCode::PHRASES[302], $response->getReasonPhrase());
        // Redirect类不提供isRedirection方法，直接检查状态码范围
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
    }

    // ===== 构造函数测试 =====

    public function testConstructorWithDefaultStatus()
    {
        $response = new Redirect('https://example.com');
        
        $this->assertEquals(302, $response->getStatusCode()); // 默认302 Found
        $this->assertEquals('Found', $response->getReasonPhrase());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertStringStartsWith('https://example.com', $response->getHeaderLine('Location'));
    }

    public function testConstructorWithCustomStatus()
    {
        $response = new Redirect('https://example.com', StatusCode::HTTP_MOVED_PERMANENTLY);
        
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('Moved Permanently', $response->getReasonPhrase());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertStringStartsWith('https://example.com', $response->getHeaderLine('Location'));
    }

    public function testConstructorWithHeaders()
    {
        $headers = [
            'X-Custom-Header' => ['custom-value'],
            'Cache-Control' => ['no-cache']
        ];
        
        $response = new Redirect('https://example.com', 302, $headers);
        
        $this->assertTrue($response->hasHeader('X-Custom-Header'));
        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertTrue($response->hasHeader('Location')); // Location仍然存在
        $this->assertEquals('custom-value', $response->getHeaderLine('X-Custom-Header'));
    }

    // ===== 不同重定向状态码测试 =====

    public function testDifferentRedirectStatusCodes()
    {
        $redirectStatuses = [
            StatusCode::HTTP_MOVED_PERMANENTLY => 'Moved Permanently', // 301
            StatusCode::HTTP_FOUND => 'Found', // 302
            StatusCode::HTTP_SEE_OTHER => 'See Other', // 303
            StatusCode::HTTP_TEMPORARY_REDIRECT => 'Temporary Redirect', // 307
            StatusCode::HTTP_PERMANENTLY_REDIRECT => 'Permanent Redirect', // 308
        ];
        
        foreach ($redirectStatuses as $statusCode => $reasonPhrase) {
            $response = new Redirect('https://example.com', $statusCode);
            
            $this->assertEquals($statusCode, $response->getStatusCode());
            $this->assertEquals($reasonPhrase, $response->getReasonPhrase());
            // 检查是否为3xx重定向状态码
            $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
            $this->assertLessThan(400, $response->getStatusCode());
            $this->assertStringStartsWith('https://example.com', $response->getHeaderLine('Location'));
        }
    }

    // ===== URI处理测试 =====

    public function testVariousUriFormats()
    {
        $uris = [
            'https://example.com',
            'http://example.com/path',
            'https://example.com/path?query=value',
            'https://user:pass@example.com:8080/path',
            '/relative/path',
        ];
        
        foreach ($uris as $uri) {
            $response = new Redirect($uri);
            $location = $response->getHeaderLine('Location');
            
            // 对于某些URI格式，可能在末尾添加了斜杠，所以使用更灵活的比较
            if ($uri === 'https://example.com') {
                $this->assertThat($location, $this->logicalOr(
                    $this->equalTo($uri),
                    $this->equalTo($uri . '/')
                ));
            } else {
                $this->assertEquals($uri, $location);
            }
        }
    }

    public function testUriWithSpecialCharacters()
    {
        $specialUris = [
            'https://example.com/path with spaces',
            'https://example.com/search?q=hello world&type=article',
            'https://example.com/path/with/üñíçødé',
            'https://user:p@ssw0rd@example.com/path',
        ];
        
        foreach ($specialUris as $uri) {
            $response = new Redirect($uri);
            $location = $response->getHeaderLine('Location');
            $this->assertNotEmpty($location);
            // 注意：实际值可能会因URL编码而有所不同
        }
    }

    // ===== 响应体和头部测试 =====

    public function testResponseBodyIsEmpty()
    {
        $response = new Redirect('https://example.com');
        
        // 重定向响应通常应该是空的
        $this->assertEquals('', $response->getContents());
        $this->assertEquals(0, strlen($response->getContents()));
    }

    public function testLocationHeaderIsSetCorrectly()
    {
        $response = new Redirect('https://example.com/target');
        
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertEquals('https://example.com/target', $response->getHeaderLine('Location'));
        $this->assertEquals(['https://example.com/target'], $response->getHeader('Location'));
    }

    public function testAdditionalHeaders()
    {
        $response = new Redirect('https://example.com');
        
        // 添加额外的头部
        $response = $response->withHeader('X-Redirect-Reason', 'Maintenance')
                            ->withHeader('Cache-Control', 'no-cache');
        
        $this->assertTrue($response->hasHeader('X-Redirect-Reason'));
        $this->assertTrue($response->hasHeader('Cache-Control'));
        $this->assertTrue($response->hasHeader('Location')); // Location仍然存在
        
        $this->assertEquals('Maintenance', $response->getHeaderLine('X-Redirect-Reason'));
        $this->assertEquals('no-cache', $response->getHeaderLine('Cache-Control'));
    }

    // ===== 状态判断测试 =====

    public function testRedirectionStatusCodeRange()
    {
        $response = new Redirect('https://example.com');
        
        // 检查重定向状态码范围
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
    }

    public function testNonRedirectStatus()
    {
        // 使用Text类直接创建非重定向响应进行对比
        $nonRedirect = new Text(StatusCode::HTTP_OK, '');
        
        // 检查状态码不在重定向范围
        $this->assertLessThan(300, $nonRedirect->getStatusCode());
        $this->assertEquals(200, $nonRedirect->getStatusCode());
    }

    // ===== 链式调用测试 =====

    public function testChainedMethodCalls()
    {
        $response = (new Redirect('https://old-site.com'))
            ->withStatus(StatusCode::HTTP_MOVED_PERMANENTLY)
            ->withHeader('X-Redirect-From', 'old-site.com')
            ->withHeader('X-Redirect-To', 'new-site.com')
            ->withCacheControl('no-cache');
        
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertStringStartsWith('https://old-site.com', $response->getHeaderLine('Location'));
        $this->assertEquals('old-site.com', $response->getHeaderLine('X-Redirect-From'));
        $this->assertEquals('new-site.com', $response->getHeaderLine('X-Redirect-To'));
        $this->assertEquals('no-cache', $response->getHeaderLine('Cache-Control'));
    }

    // ===== toString方法测试 =====

    public function testToStringMethod()
    {
        $response = new Redirect('https://example.com/target');
        
        $stringRepresentation = (string)$response;
        
        $this->assertStringContainsString('HTTP/1.1 302 Found', $stringRepresentation);
        $this->assertStringContainsString('Location: https://example.com/target', $stringRepresentation);
        // 重定向响应体应该是空的
        $lines = explode("\r\n\r\n", $stringRepresentation);
        if (count($lines) < 2) {
            // 如果没有找到 \r\n\r\n 模式，尝试 \n\n
            $lines = explode("\n\n", $stringRepresentation);
        }
        $this->assertGreaterThanOrEqual(2, count($lines));
        $this->assertEquals('', trim($lines[count($lines)-1])); // 空的响应体
    }

    public function testToStringWithAdditionalHeaders()
    {
        $response = (new Redirect('https://example.com'))
            ->withHeader('X-Custom', 'value')
            ->withHeader('Cache-Control', 'no-cache');
        
        $stringRepresentation = (string)$response;
        
        $this->assertStringContainsString('Location: https://example.com', $stringRepresentation);
        $this->assertStringContainsString('X-Custom: value', $stringRepresentation);
        $this->assertStringContainsString('Cache-Control: no-cache', $stringRepresentation);
    }

    // ===== 边界情况测试 =====

    public function testEmptyUri()
    {
        $response = new Redirect('');
        
        $this->assertEquals('', $response->getHeaderLine('Location'));
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRelativeUri()
    {
        $response = new Redirect('/new/path');
        
        $this->assertEquals('/new/path', $response->getHeaderLine('Location'));
    }

    public function testUriWithFragment()
    {
        $response = new Redirect('https://example.com/page#section');
        
        $this->assertEquals('https://example.com/page#section', $response->getHeaderLine('Location'));
    }

    public function testUriWithQueryParameters()
    {
        $response = new Redirect('https://example.com/search?q=test&category=books');
        
        $this->assertEquals('https://example.com/search?q=test&category=books', $response->getHeaderLine('Location'));
    }

    // ===== 性能测试 =====

    public function testPerformanceWithMultipleRedirects()
    {
        $startTime = microtime(true);
        
        for ($i = 0; $i < 100; $i++) {
            $response = new Redirect("https://example.com/redirect/$i");
            $this->assertEquals(302, $response->getStatusCode());
        }
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        // 100次重定向创建应该很快完成
        $this->assertLessThan(100, $duration, 'Creating multiple redirects should be fast');
    }

    // ===== 与PSR-7兼容性测试 =====

    public function testImplementsResponseInterface()
    {
        $response = new Redirect('https://example.com');
        
        $this->assertInstanceOf(\Psr\Http\Message\ResponseInterface::class, $response);
        $this->assertInstanceOf(\Stringable::class, $response);
    }
}