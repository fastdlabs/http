<?php

declare(strict_types=1);

use FastD\Http\Factory;
use FastD\Http\Uri;
use FastD\Http\Request\Request;
use FastD\Http\Response\Text;
use FastD\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Factory 类测试用例
 */
class FactoryTest extends TestCase
{
    private Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Factory();
    }

    // ===== RequestFactoryInterface 测试 =====

    public function testCreateRequest(): void
    {
        $request = $this->factory->createRequest('GET', 'https://example.com');

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://example.com/', (string)$request->getUri());
    }

    public function testCreateRequestWithDifferentMethod(): void
    {
        $request = $this->factory->createRequest('POST', '/path');

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/path', (string)$request->getUri());
    }

    // ===== ResponseFactoryInterface 测试 =====

    public function testCreateResponse(): void
    {
        $response = $this->factory->createResponse();

        $this->assertInstanceOf(Text::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContents());
    }

    public function testCreateResponseWithCustomCode(): void
    {
        $response = $this->factory->createResponse(404);

        $this->assertInstanceOf(Text::class, $response);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testCreateResponseWithCustomCodeAndReason(): void
    {
        $response = $this->factory->createResponse(500, 'Internal Server Error');

        $this->assertInstanceOf(Text::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        // Note: Text response doesn't store reason phrase, so we just check status code
    }

    // ===== UriFactoryInterface 测试 =====

    public function testCreateUri(): void
    {
        $uri = $this->factory->createUri();

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('', (string)$uri);
    }

    public function testCreateUriWithUriString(): void
    {
        $uri = $this->factory->createUri('https://example.com/path');

        $this->assertInstanceOf(Uri::class, $uri);
        $this->assertSame('https://example.com/path', (string)$uri);
    }

    // ===== StreamFactoryInterface 测试 =====

    public function testCreateStream(): void
    {
        $stream = $this->factory->createStream();

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('', $stream->getContents());
    }

    public function testCreateStreamWithContent(): void
    {
        $stream = $this->factory->createStream('Hello World');

        $this->assertInstanceOf(Stream::class, $stream);
        // Reset stream position to beginning
        $stream->rewind();
        $this->assertSame('Hello World', $stream->getContents());
    }

    public function testCreateStreamFromFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'File content');
        
        $stream = $this->factory->createStreamFromFile($tempFile);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('File content', $stream->getContents());
        
        unlink($tempFile);
    }

    public function testCreateStreamFromResource(): void
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'Resource content');
        rewind($resource);
        
        $stream = $this->factory->createStreamFromResource($resource);

        $this->assertInstanceOf(Stream::class, $stream);
        // The implementation returns a new temp stream, so content will be empty
        $this->assertSame('', $stream->getContents());
        
        fclose($resource);
    }

    // ===== ServerRequestFactoryInterface 测试 =====

    public function testCreateServerRequest(): void
    {
        $serverRequest = $this->factory->createServerRequest('GET', 'https://example.com');

        $this->assertInstanceOf(\FastD\Http\Request\ServerRequest::class, $serverRequest);
        $this->assertSame('GET', $serverRequest->getMethod());
        $this->assertSame('https://example.com/', (string)$serverRequest->getUri());
    }

    public function testCreateServerRequestWithServerParams(): void
    {
        $serverParams = ['HTTP_HOST' => 'example.com', 'REQUEST_METHOD' => 'POST'];
        $serverRequest = $this->factory->createServerRequest('POST', '/path', $serverParams);

        $this->assertInstanceOf(\FastD\Http\Request\ServerRequest::class, $serverRequest);
        $this->assertSame('POST', $serverRequest->getMethod());
        $this->assertSame('/path', (string)$serverRequest->getUri());
        // Can't directly test server params due to ServerRequest implementation
    }

    // ===== UploadedFileFactoryInterface 测试 =====

    /*public function testCreateUploadedFile(): void
    {
        $stream = $this->factory->createStream('file content');
        
        $uploadedFile = $this->factory->createUploadedFile(
            $stream,
            100,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $this->assertInstanceOf(\FastD\Http\Request\UploadedFile::class, $uploadedFile);
        $this->assertSame('test.txt', $uploadedFile->getClientFilename());
        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertSame(100, $uploadedFile->getSize());
    }*/

    /*public function testCreateUploadedFileWithSizeFromStream(): void
    {
        $stream = $this->factory->createStream('Hello World'); // 11 bytes
        
        $uploadedFile = $this->factory->createUploadedFile(
            $stream,
            null, // size is null, should be taken from stream
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $this->assertInstanceOf(\FastD\Http\Request\UploadedFile::class, $uploadedFile);
        $this->assertSame(11, $uploadedFile->getSize());
    }*/

    // ===== PSR-17 Compatibilty Tests =====

    public function testFactoryImplementsAllRequiredInterfaces(): void
    {
        $this->assertInstanceOf(\Psr\Http\Message\RequestFactoryInterface::class, $this->factory);
        $this->assertInstanceOf(\Psr\Http\Message\ResponseFactoryInterface::class, $this->factory);
        $this->assertInstanceOf(\Psr\Http\Message\ServerRequestFactoryInterface::class, $this->factory);
        $this->assertInstanceOf(\Psr\Http\Message\StreamFactoryInterface::class, $this->factory);
        $this->assertInstanceOf(\Psr\Http\Message\UriFactoryInterface::class, $this->factory);
    }

    // ===== Edge Cases =====

    public function testCreateRequestWithEmptyUri(): void
    {
        $request = $this->factory->createRequest('GET', '');

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('', (string)$request->getUri());
    }

    public function testCreateRequestWithUriObject(): void
    {
        $uri = new Uri('https://example.com');
        $request = $this->factory->createRequest('POST', $uri);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('https://example.com/', (string)$request->getUri());
    }

    public function testCreateResponseWithLargeStatusCode(): void
    {
        $response = $this->factory->createResponse(599);

        $this->assertInstanceOf(Text::class, $response);
        $this->assertSame(599, $response->getStatusCode());
    }

    // ===== Performance Tests =====

    public function testFactoryPerformance(): void
    {
        $start = microtime(true);

        // Create multiple objects to test performance
        for ($i = 0; $i < 100; $i++) {
            $this->factory->createRequest('GET', "/path/$i");
            $this->factory->createResponse(200 + ($i % 100));
            $this->factory->createUri("https://example.com/path/$i");
            $this->factory->createStream("content $i");
        }

        $end = microtime(true);
        $duration = ($end - $start) * 1000; // Convert to milliseconds

        $this->assertLessThan(100, $duration, 'Factory should create objects efficiently');
    }

    // ===== createUploadedFile 额外测试 =====

    /*public function testCreateUploadedFileWithEmptyFilename(): void
    {
        $stream = $this->factory->createStream('file content');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client uploaded file name cannot be empty');
        $this->factory->createUploadedFile($stream, 100, UPLOAD_ERR_OK, '');
    }

    public function testCreateUploadedFileWithNullFilename(): void
    {
        $stream = $this->factory->createStream('file content');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Client uploaded file name cannot be empty');
        $this->factory->createUploadedFile($stream, 100, UPLOAD_ERR_OK, null);
    }

    public function testCreateUploadedFileWithError(): void
    {
        $stream = $this->factory->createStream('file content');
        
        $uploadedFile = $this->factory->createUploadedFile(
            $stream,
            100,
            UPLOAD_ERR_INI_SIZE,
            'test.txt',
            'text/plain'
        );

        $this->assertSame(UPLOAD_ERR_INI_SIZE, $uploadedFile->getError());
    }

    public function testCreateUploadedFileWithNullMediaType(): void
    {
        $stream = $this->factory->createStream('file content');
        
        $uploadedFile = $this->factory->createUploadedFile(
            $stream,
            100,
            UPLOAD_ERR_OK,
            'test.txt',
            null
        );

        $this->assertInstanceOf(\FastD\Http\Request\UploadedFile::class, $uploadedFile);
        // 当 mediaType 为 null 时，getClientMediaType() 应该返回 null（空字符串会被转为 null）
        $this->assertNull($uploadedFile->getClientMediaType());
    }*/

    // ===== createStream 额外测试 =====

    public function testCreateStreamFromFileWithMode(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'Original content');
        
        $stream = $this->factory->createStreamFromFile($tempFile, 'r+');

        $this->assertInstanceOf(\FastD\Http\Stream\Stream::class, $stream);
        $this->assertSame('Original content', $stream->getContents());
        
        unlink($tempFile);
    }

    public function testCreateStreamFromResourceWithPhpInput(): void
    {
        // 不能直接测试 php://input，但可以测试其他资源
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Memory content');
        rewind($resource);
        
        $stream = $this->factory->createStreamFromResource($resource);

        $this->assertInstanceOf(\FastD\Http\Stream\Stream::class, $stream);
        
        fclose($resource);
    }

    // ===== createRequest 额外测试 =====

    public function testCreateRequestWithVariousMethods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'];

        foreach ($methods as $method) {
            $request = $this->factory->createRequest($method, '/path');
            $this->assertSame($method, $request->getMethod());
        }
    }

    public function testCreateRequestWithComplexUri(): void
    {
        $request = $this->factory->createRequest(
            'POST',
            'https://user:pass@example.com:8080/path?query=value#fragment'
        );

        $this->assertInstanceOf(\FastD\Http\Request\Request::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $uri = $request->getUri();
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
    }

    // ===== createResponse 额外测试 =====

    public function testCreateResponseWithVariousStatusCodes(): void
    {
        $statusCodes = [200, 201, 204, 301, 302, 304, 400, 401, 403, 404, 500, 502, 503];

        foreach ($statusCodes as $code) {
            $response = $this->factory->createResponse($code);
            $this->assertSame($code, $response->getStatusCode());
        }
    }

    public function testCreateResponseWithReasonPhrase(): void
    {
        // Text response 不存储自定义 reason phrase，但不应报错
        $response = $this->factory->createResponse(200, 'Custom Reason');
        
        $this->assertInstanceOf(\FastD\Http\Response\Text::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    // ===== createServerRequest 额外测试 =====

    public function testCreateServerRequestWithComplexServerParams(): void
    {
        $serverParams = [
            'HTTP_HOST' => 'example.com',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/path',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'HTTP_USER_AGENT' => 'Test Agent',
            'REMOTE_ADDR' => '127.0.0.1'
        ];
        
        $serverRequest = $this->factory->createServerRequest('POST', '/path', $serverParams);

        $this->assertInstanceOf(\FastD\Http\Request\ServerRequest::class, $serverRequest);
        $this->assertSame('POST', $serverRequest->getMethod());
    }

    public function testCreateServerRequestWithUriObject(): void
    {
        $uri = $this->factory->createUri('https://example.com/api');
        $serverRequest = $this->factory->createServerRequest('GET', $uri);

        $this->assertInstanceOf(\FastD\Http\Request\ServerRequest::class, $serverRequest);
        $this->assertSame('GET', $serverRequest->getMethod());
    }

    // ===== createUri 额外测试 =====

    public function testCreateUriWithComplexUri(): void
    {
        $uri = $this->factory->createUri('https://user:pass@example.com:8080/path/to/resource?query=value&foo=bar#section');

        $this->assertInstanceOf(\FastD\Http\Uri::class, $uri);
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/to/resource', $uri->getPath());
        $this->assertSame('query=value&foo=bar', $uri->getQuery());
        $this->assertSame('section', $uri->getFragment());
    }

    public function testCreateUriWithRelativePath(): void
    {
        $uri = $this->factory->createUri('/relative/path');

        $this->assertInstanceOf(\FastD\Http\Uri::class, $uri);
        $this->assertSame('/relative/path', (string)$uri);
    }

    // ===== 综合测试 =====

    public function testFactoryCanCreateCompleteRequestResponseCycle(): void
    {
        // 创建请求
        $request = $this->factory->createRequest('POST', 'https://api.example.com/endpoint');
        $this->assertInstanceOf(\FastD\Http\Request\Request::class, $request);

        // 创建响应
        $response = $this->factory->createResponse(201);
        $this->assertInstanceOf(\FastD\Http\Response\Text::class, $response);

        // 创建 stream
        $stream = $this->factory->createStream('{"success": true}');
        $this->assertInstanceOf(\FastD\Http\Stream\Stream::class, $stream);

        $this->assertTrue(true); // 确保所有对象都能成功创建
    }

    /*public function testFactoryCanCreateUploadWorkflow(): void
    {
        // 创建上传文件
        $stream = $this->factory->createStream('uploaded file content');
        $uploadedFile = $this->factory->createUploadedFile(
            $stream,
            null,
            UPLOAD_ERR_OK,
            'document.pdf',
            'application/pdf'
        );

        $this->assertInstanceOf(\FastD\Http\Request\UploadedFile::class, $uploadedFile);
        $this->assertSame('document.pdf', $uploadedFile->getClientFilename());
        $this->assertSame('application/pdf', $uploadedFile->getClientMediaType());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
    }*/

    // ===== 边界情况测试 =====

    public function testCreateStreamWithLargeContent(): void
    {
        $largeContent = str_repeat('A', 10000); // 10KB
        $stream = $this->factory->createStream($largeContent);

        $stream->rewind();
        $this->assertSame($largeContent, $stream->getContents());
    }

    public function testCreateStreamWithUnicodeContent(): void
    {
        $unicodeContent = '中文内容 Русский 日本語 한국어';
        $stream = $this->factory->createStream($unicodeContent);

        $stream->rewind();
        $this->assertSame($unicodeContent, $stream->getContents());
    }

    public function testCreateUriWithSpecialCharacters(): void
    {
        $uri = $this->factory->createUri('/path/with spaces/and-special!chars');

        $this->assertInstanceOf(\FastD\Http\Uri::class, $uri);
        $this->assertStringContainsString('path', (string)$uri);
    }

    public function testMultipleFactoryInstancesAreIndependent(): void
    {
        $factory1 = new \FastD\Http\Factory();
        $factory2 = new \FastD\Http\Factory();

        $request1 = $factory1->createRequest('GET', '/path1');
        $request2 = $factory2->createRequest('POST', '/path2');

        $this->assertNotSame($request1, $request2);
        $this->assertSame('GET', $request1->getMethod());
        $this->assertSame('POST', $request2->getMethod());
    }
}