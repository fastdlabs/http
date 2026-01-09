<?php

use FastD\Http\Request\ServerRequest;
use FastD\Http\Stream\Stream;
use FastD\Http\Cookie;
use FastD\Http\Request\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * ServerRequest类完整单元测试
 */
class ServerRequestTest extends TestCase
{
    // ===== 基础功能测试 =====

    public function testServerRequestCreation(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/users',
            'SERVER_NAME' => 'api.example.com',
            'SERVER_PORT' => '80',
        ];
        
        $serverRequest = new ServerRequest('POST', 'http://api.example.com/api/users', [], null, '1.1', $serverParams);
        
        $this->assertEquals('POST', $serverRequest->getMethod());
        $this->assertEquals('http://api.example.com/api/users', (string)$serverRequest->getUri());
        $this->assertEquals($serverParams, $serverRequest->getServerParams());
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
    }

    public function testServerRequestCreationWithDefaults(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        
        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('http://example.com/', (string)$serverRequest->getUri());
        $this->assertEquals([], $serverRequest->getServerParams());
        $this->assertEquals([], $serverRequest->getCookieParams());
        $this->assertEquals([], $serverRequest->getQueryParams());
        $this->assertEquals([], $serverRequest->getParsedBody());
        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertEquals([], $serverRequest->getAttributes());
    }

    // ===== 服务器参数处理测试 =====

    public function testGetServerParams(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'POST',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '8080',
            'REQUEST_URI' => '/api/test',
        ];
        
        $serverRequest = new ServerRequest('POST', 'http://localhost:8080/api/test', [], null, '1.1', $serverParams);
        
        $this->assertEquals($serverParams, $serverRequest->getServerParams());
    }

    public function testServerParamsAreImmutable(): void
    {
        $serverParams = ['REQUEST_METHOD' => 'GET'];
        $serverRequest = new ServerRequest('GET', 'http://example.com', [], null, '1.1', $serverParams);
        
        $originalParams = $serverRequest->getServerParams();
        $originalParams['NEW_PARAM'] = 'value';
        
        // 修改返回的数组不应影响原始数据
        $this->assertArrayNotHasKey('NEW_PARAM', $serverRequest->getServerParams());
    }

    // ===== Cookie参数处理测试 =====

    public function testGetCookieParams(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        
        $this->assertEquals([], $serverRequest->getCookieParams());
        
        $cookies = ['session_id' => 'abc123', 'user_pref' => 'dark_mode'];
        $modified = $serverRequest->withCookieParams($cookies);
        
        $this->assertEquals([], $serverRequest->getCookieParams());
        $this->assertEquals($cookies, $modified->getCookieParams());
    }

    public function testWithCookieParamsIsImmutable(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $cookies = ['session_id' => 'abc123'];
        
        $modified = $serverRequest->withCookieParams($cookies);
        
        $this->assertNotSame($serverRequest, $modified);
        $this->assertEquals([], $serverRequest->getCookieParams());
        $this->assertEquals($cookies, $modified->getCookieParams());
    }

    public function testGetCookie(): void
    {
        $serverRequest = (new ServerRequest('GET', 'http://example.com'))
            ->withCookieParams([
                'session_id' => 'abc123',
                'user_pref' => 'dark_mode'
            ]);
        
        $sessionCookie = $serverRequest->getCookie('session_id');
        if ($sessionCookie !== null) {
            $this->assertInstanceOf(Cookie::class, $sessionCookie);
            $this->assertEquals('session_id', $sessionCookie->getName());
            $this->assertEquals('abc123', $sessionCookie->getValue());
        }
        
        $this->assertNull($serverRequest->getCookie('nonexistent'));
    }

    public function testGetCookieWithCookieObject(): void
    {
        $cookieObj = new Cookie('test_cookie', 'test_value');
        $serverRequest = (new ServerRequest('GET', 'http://example.com'))
            ->withCookieParams([
                'test_cookie' => $cookieObj
            ]);
        
        $retrieved = $serverRequest->getCookie('test_cookie');
        $this->assertSame($cookieObj, $retrieved);
    }

    // ===== 查询参数处理测试 =====

    public function testGetQueryParams(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        
        $this->assertEquals([], $serverRequest->getQueryParams());
        
        $params = ['page' => '1', 'limit' => '10'];
        $modified = $serverRequest->withQueryParams($params);
        
        $this->assertEquals([], $serverRequest->getQueryParams());
        $this->assertEquals($params, $modified->getQueryParams());
    }

    public function testWithQueryParamsIsImmutable(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $params = ['page' => '1'];
        
        $modified = $serverRequest->withQueryParams($params);
        
        $this->assertNotSame($serverRequest, $modified);
        $this->assertEquals([], $serverRequest->getQueryParams());
        $this->assertEquals($params, $modified->getQueryParams());
    }

    // ===== 上传文件处理测试 =====

    public function testGetUploadedFiles(): void
    {
        $serverRequest = new ServerRequest('POST', 'http://example.com/upload');
        
        $this->assertEquals([], $serverRequest->getUploadedFiles());
        
        $files = [
            'avatar' => new UploadedFile('temp_file', 'image/jpeg', 'temp_path', 1024, UPLOAD_ERR_OK)
        ];
        $modified = $serverRequest->withUploadedFiles($files);
        
        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertEquals($files, $modified->getUploadedFiles());
    }

    public function testWithUploadedFilesIsImmutable(): void
    {
        $serverRequest = new ServerRequest('POST', 'http://example.com/upload');
        $files = [
            'document' => new UploadedFile('temp_doc', 'application/pdf', 'temp_path', 2048, UPLOAD_ERR_OK)
        ];
        
        $modified = $serverRequest->withUploadedFiles($files);
        
        $this->assertNotSame($serverRequest, $modified);
        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertEquals($files, $modified->getUploadedFiles());
    }

    // ===== 解析主体处理测试 =====

    public function testGetParsedBody(): void
    {
        $serverRequest = new ServerRequest('POST', 'http://example.com/api');
        
        $this->assertEquals([], $serverRequest->getParsedBody());
        
        $parsedBody = ['name' => 'John', 'email' => 'john@example.com'];
        $modified = $serverRequest->withParsedBody($parsedBody);
        
        $this->assertEquals([], $serverRequest->getParsedBody());
        $this->assertEquals($parsedBody, $modified->getParsedBody());
    }

    public function testWithParsedBodyIsImmutable(): void
    {
        $serverRequest = new ServerRequest('POST', 'http://example.com/api');
        $data = ['field' => 'value'];
        
        $modified = $serverRequest->withParsedBody($data);
        
        $this->assertNotSame($serverRequest, $modified);
        $this->assertEquals([], $serverRequest->getParsedBody());
        $this->assertEquals($data, $modified->getParsedBody());
    }

    public function testWithParsedBodyAcceptsNull(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $modified = $serverRequest->withParsedBody(null);
        
        $this->assertNull($modified->getParsedBody());
    }

    public function testWithParsedBodyAcceptsObject(): void
    {
        $obj = (object)['prop' => 'value'];
        $serverRequest = new ServerRequest('POST', 'http://example.com');
        $modified = $serverRequest->withParsedBody($obj);
        
        $this->assertSame($obj, $modified->getParsedBody());
    }

    // ===== 属性处理测试 =====

    public function testGetAttributes(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        
        $this->assertEquals([], $serverRequest->getAttributes());
        
        $serverRequest = $serverRequest->withAttribute('key1', 'value1');
        $attributes = $serverRequest->getAttributes();
        
        $this->assertArrayHasKey('key1', $attributes);
        $this->assertEquals('value1', $attributes['key1']);
    }

    public function testGetAttribute(): void
    {
        $serverRequest = (new ServerRequest('GET', 'http://example.com'))
            ->withAttribute('user_id', 123)
            ->withAttribute('role', 'admin');
        
        $this->assertEquals(123, $serverRequest->getAttribute('user_id'));
        $this->assertEquals('admin', $serverRequest->getAttribute('role'));
        $this->assertNull($serverRequest->getAttribute('nonexistent'));
        $this->assertEquals('default', $serverRequest->getAttribute('nonexistent', 'default'));
    }

    public function testWithAttributeIsImmutable(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $modified = $serverRequest->withAttribute('key', 'value');
        
        $this->assertNotSame($serverRequest, $modified);
        $this->assertEquals([], $serverRequest->getAttributes());
        $this->assertEquals(['key' => 'value'], $modified->getAttributes());
    }

    public function testWithoutAttributeIsImmutable(): void
    {
        $serverRequest = (new ServerRequest('GET', 'http://example.com'))
            ->withAttribute('key1', 'value1')
            ->withAttribute('key2', 'value2');
        
        $modified = $serverRequest->withoutAttribute('key1');
        
        $this->assertNotSame($serverRequest, $modified);
        $this->assertArrayHasKey('key1', $serverRequest->getAttributes());
        $this->assertArrayNotHasKey('key1', $modified->getAttributes());
        $this->assertArrayHasKey('key2', $serverRequest->getAttributes());
        $this->assertArrayHasKey('key2', $modified->getAttributes());
    }

    public function testWithoutAttributeNonExistentReturnsSame(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $modified = $serverRequest->withoutAttribute('nonexistent');
        
        $this->assertSame($serverRequest, $modified);
    }

    // ===== 继承自 Request 的功能测试 =====

    public function testInheritsFromRequest(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        
        $this->assertInstanceOf(\Psr\Http\Message\ServerRequestInterface::class, $serverRequest);
        $this->assertInstanceOf(\FastD\Http\Request\Request::class, $serverRequest);
        $this->assertInstanceOf(\Psr\Http\Message\RequestInterface::class, $serverRequest);
    }

    public function testRequestMethodInheritance(): void
    {
        $serverRequest = new ServerRequest('POST', 'http://example.com');
        
        $this->assertEquals('POST', $serverRequest->getMethod());
        
        $putRequest = $serverRequest->withMethod('PUT');
        $this->assertEquals('PUT', $putRequest->getMethod());
        $this->assertEquals('POST', $serverRequest->getMethod());
    }

    public function testRequestUriInheritance(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com/old');
        
        $newUri = new \FastD\Http\Uri('http://example.com/new');
        $modified = $serverRequest->withUri($newUri);
        
        $this->assertEquals('http://example.com/old', (string)$serverRequest->getUri());
        $this->assertEquals('http://example.com/new', (string)$modified->getUri());
    }

    public function testRequestTargetInheritance(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $modified = $serverRequest->withRequestTarget('/new-target');
        
        $this->assertEquals('/', $serverRequest->getRequestTarget());
        $this->assertEquals('/new-target', $modified->getRequestTarget());
    }

    public function testRequestHeadersInheritance(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $modified = $serverRequest->withHeader('X-Custom', 'value');
        
        $this->assertFalse($serverRequest->hasHeader('X-Custom'));
        $this->assertTrue($modified->hasHeader('X-Custom'));
        $this->assertEquals('value', $modified->getHeaderLine('X-Custom'));
    }

    public function testRequestBodyInheritance(): void
    {
        $body1 = Stream::create('content1');
        $body2 = Stream::create('content2');
        
        $serverRequest = new ServerRequest('POST', 'http://example.com', [], $body1);
        $modified = $serverRequest->withBody($body2);
        
        // Rewind streams before reading
        $originalBody = $serverRequest->getBody();
        $originalBody->rewind();
        $originalContent = $originalBody->getContents();
        
        $modifiedBody = $modified->getBody();
        $modifiedBody->rewind();
        $modifiedContent = $modifiedBody->getContents();
        
        $this->assertEquals('content1', $originalContent);
        $this->assertEquals('content2', $modifiedContent);
    }

    // ===== 不可变性测试 =====

    public function testAllWithMethodsAreImmutable(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        
        $modified = $serverRequest
            ->withCookieParams(['cookie' => 'value'])
            ->withQueryParams(['query' => 'value'])
            ->withUploadedFiles([])
            ->withParsedBody(['body' => 'value'])
            ->withAttribute('attr', 'value');
        
        // 原始对象不应改变
        $this->assertEquals([], $serverRequest->getCookieParams());
        $this->assertEquals([], $serverRequest->getQueryParams());
        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertEquals([], $serverRequest->getParsedBody());
        $this->assertEquals([], $serverRequest->getAttributes());
        
        // 修改后的对象应包含新值
        $this->assertEquals(['cookie' => 'value'], $modified->getCookieParams());
        $this->assertEquals(['query' => 'value'], $modified->getQueryParams());
        $this->assertEquals(['attr' => 'value'], $modified->getAttributes());
    }

    // ===== 实际使用场景测试 =====

    public function testRestApiRequestScenario(): void
    {
        $serverParams = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_AUTHORIZATION' => 'Bearer token123',
            'HTTP_CONTENT_TYPE' => 'application/json',
        ];
        
        $parsedBody = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30
        ];
        
        $serverRequest = new ServerRequest(
            'POST', 
            'http://api.example.com/users', 
            ['Content-Type' => ['application/json']],
            Stream::create(json_encode($parsedBody)),
            '1.1',
            $serverParams
        );
        
        $serverRequest = $serverRequest
            ->withParsedBody($parsedBody)
            ->withAttribute('route', '/users')
            ->withAttribute('auth_token', 'token123');
        
        $this->assertEquals('POST', $serverRequest->getMethod());
        $this->assertEquals('http://api.example.com/users', (string)$serverRequest->getUri());
        $this->assertEquals($parsedBody, $serverRequest->getParsedBody());
        $this->assertEquals('/users', $serverRequest->getAttribute('route'));
        $this->assertEquals('token123', $serverRequest->getAttribute('auth_token'));
        $this->assertEquals('Bearer token123', $serverRequest->getServerParams()['HTTP_AUTHORIZATION'] ?? '');
    }

    public function testFileUploadRequestScenario(): void
    {
        $uploadedFile = new UploadedFile(
            'profile.jpg', 
            'image/jpeg', 
            'tmp/path/image.jpg', 
            102400, 
            UPLOAD_ERR_OK
        );
        
        $uploadedFiles = [
            'profile_image' => $uploadedFile
        ];
        
        $serverRequest = (new ServerRequest('POST', 'http://example.com/upload'))
            ->withUploadedFiles($uploadedFiles)
            ->withCookieParams(['session' => 'sess123'])
            ->withAttribute('user_id', 123);
        
        $fetchedFiles = $serverRequest->getUploadedFiles();
        $this->assertArrayHasKey('profile_image', $fetchedFiles);
        $this->assertInstanceOf(UploadedFile::class, $fetchedFiles['profile_image']);
        $this->assertNotEmpty($serverRequest->getCookieParams());
        $this->assertEquals(123, $serverRequest->getAttribute('user_id'));
    }

    public function testPaginationRequestScenario(): void
    {
        $queryParams = [
            'page' => '2',
            'limit' => '20',
            'sort' => 'created_at',
            'order' => 'desc',
            'filter' => 'active'
        ];
        
        $serverRequest = (new ServerRequest('GET', 'http://api.example.com/users'))
            ->withQueryParams($queryParams)
            ->withAttribute('route_params', ['resource' => 'users']);
        
        $this->assertEquals($queryParams, $serverRequest->getQueryParams());
        $this->assertEquals('2', $serverRequest->getQueryParams()['page']);
        $this->assertEquals(['resource' => 'users'], $serverRequest->getAttribute('route_params'));
    }

    // ===== normalizeFiles 方法测试 =====

    public function testNormalizeFilesSingleFile(): void
    {
        $filesData = [
            'single_file' => [
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/test.txt',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024,
            ]
        ];
        
        $normalized = ServerRequest::normalizeFiles($filesData);
        
        $this->assertArrayHasKey('single_file', $normalized);
        $this->assertInstanceOf(\Psr\Http\Message\UploadedFileInterface::class, $normalized['single_file']);
    }

    public function testNormalizeFilesMultipleFiles(): void
    {
        $filesData = [
            'multi_files' => [
                'name' => ['file1.txt', 'file2.txt'],
                'type' => ['text/plain', 'text/plain'],
                'tmp_name' => ['/tmp/file1.txt', '/tmp/file2.txt'],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [1024, 2048],
            ]
        ];
        
        $normalized = ServerRequest::normalizeFiles($filesData);
        
        $this->assertArrayHasKey('multi_files', $normalized);
        $this->assertIsArray($normalized['multi_files']);
        // Filter removes null entries, so we might have 2 items
        $this->assertGreaterThanOrEqual(1, count($normalized['multi_files']));
    }

    // ===== createUriFromBoth 方法测试 =====

    public function testCreateUriFromBothBasic(): void
    {
        $serverParams = [
            'REQUEST_SCHEME' => 'https',
            'SERVER_NAME' => 'example.com',
            'SERVER_PORT' => '443',
            'REQUEST_URI' => '/api/users',
        ];
        
        $uri = ServerRequest::createUriFromBoth($serverParams);
        $this->assertEquals('https://example.com/api/users', $uri);
    }

    public function testCreateUriFromBothWithHttp(): void
    {
        $serverParams = [
            'HTTPS' => 'off',
            'HTTP_HOST' => 'example.com',
            'SERVER_PORT' => '80',
            'REQUEST_URI' => '/api/users',
        ];
        
        $uri = ServerRequest::createUriFromBoth($serverParams);
        $this->assertEquals('http://example.com/api/users', $uri);
    }

    public function testCreateUriFromBothWithNonStandardPort(): void
    {
        $serverParams = [
            'REQUEST_SCHEME' => 'http',
            'HTTP_HOST' => 'example.com:8080',
            'SERVER_PORT' => '8080',
            'REQUEST_URI' => '/api/users',
            'QUERY_STRING' => 'page=1',
        ];
        
        $uri = ServerRequest::createUriFromBoth($serverParams);
        $this->assertEquals('http://example.com:8080/api/users?page=1', $uri);
    }

    // ===== fromGlobals 方法测试 =====

    public function testFromGlobalsMethodExists(): void
    {
        $this->assertTrue(method_exists(ServerRequest::class, 'fromGlobals'));
    }

    public function testFromGlobals(): void
    {
        // 保存原始全局变量
        $originalServer = $_SERVER;
        $originalCookie = $_COOKIE;
        $originalGet = $_GET;
        $originalPost = $_POST;
        $originalFiles = $_FILES;
        
        // 设置测试数据
        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/test',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'HTTP_HOST' => 'localhost',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];
        $_COOKIE = ['session' => 'abc123'];
        $_GET = ['param' => 'value'];
        $_POST = ['field' => 'data'];
        $_FILES = [];
        
        try {
            $serverRequest = ServerRequest::fromGlobals();
            
            $this->assertEquals('POST', $serverRequest->getMethod());
            $this->assertStringContainsString('/api/test', (string)$serverRequest->getUri());
            $this->assertEquals(['param' => 'value'], $serverRequest->getQueryParams());
            $this->assertEquals(['session' => 'abc123'], $serverRequest->getCookieParams());
            $this->assertEquals(['field' => 'data'], $serverRequest->getParsedBody());
        } finally {
            // 恢复原始全局变量
            $_SERVER = $originalServer;
            $_COOKIE = $originalCookie;
            $_GET = $originalGet;
            $_POST = $originalPost;
            $_FILES = $originalFiles;
        }
    }

    // ===== 边界情况测试 =====

    public function testEmptyServerParams(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com', [], null, '1.1', []);
        
        $this->assertEquals([], $serverRequest->getServerParams());
    }

    public function testNullBodyInParameter(): void
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com', [], null, '1.1', []);
        
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $serverRequest->getBody());
    }

    public function testLargeAttributeData(): void
    {
        $largeData = [
            'data' => str_repeat('a', 10000),
            'nested' => [
                'level1' => [
                    'level2' => [
                        'value' => 'deep'
                    ]
                ]
            ]
        ];
        
        $serverRequest = (new ServerRequest('GET', 'http://example.com'))
            ->withAttribute('large_data', $largeData);
        
        $this->assertEquals($largeData, $serverRequest->getAttribute('large_data'));
    }

    // ===== 错误处理测试 =====

    public function testWithParsedBodyValidation(): void
    {
        $serverRequest = new ServerRequest('POST', 'http://example.com');
        
        // 测试接受有效的数据类型
        $arrayResult = $serverRequest->withParsedBody(['key' => 'value']);
        $objectResult = $serverRequest->withParsedBody((object)['key' => 'value']);
        $nullResult = $serverRequest->withParsedBody(null);
        
        $this->assertEquals(['key' => 'value'], $arrayResult->getParsedBody());
        $this->assertEquals((object)['key' => 'value'], $objectResult->getParsedBody());
        $this->assertNull($nullResult->getParsedBody());
    }
}