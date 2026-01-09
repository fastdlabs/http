<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Cookie;
use FastD\Http\Exception\ClientException;
use FastD\Http\Exception\NetworkException;
use FastD\Http\Exception\RequestException;
use FastD\Http\Request\Client;
use FastD\Http\Request\Request;
use FastD\Http\Response\Json;
use FastD\Http\Response\StatusCode;
use FastD\Http\Response\Text;
use FastD\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;

/**
 * Client类完整单元测试
 */
class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
    }

    // ===== 构造和选项管理测试 =====

    public function testConstructorCreatesInstance(): void
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testConstructorWithCustomOptions(): void
    {
        $customOptions = [
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_USERAGENT => 'Custom Agent',
        ];
        
        $client = new Client($customOptions);
        $options = $client->getOptions();
        
        $this->assertEquals(60, $options[CURLOPT_TIMEOUT]);
        $this->assertEquals(20, $options[CURLOPT_CONNECTTIMEOUT]);
        $this->assertEquals('Custom Agent', $options[CURLOPT_USERAGENT]);
    }

    public function testDefaultOptions(): void
    {
        $options = $this->client->getOptions();
        
        $this->assertTrue($options[CURLOPT_HEADER]);
        $this->assertTrue($options[CURLOPT_RETURNTRANSFER]);
        $this->assertEquals(Client::USER_AGENT, $options[CURLOPT_USERAGENT]);
        $this->assertEquals(30, $options[CURLOPT_TIMEOUT]);
        $this->assertEquals(10, $options[CURLOPT_CONNECTTIMEOUT]);
        $this->assertFalse($options[CURLOPT_SSL_VERIFYPEER]);
        $this->assertFalse($options[CURLOPT_SSL_VERIFYHOST]);
        $this->assertTrue($options[CURLOPT_FOLLOWLOCATION]);
        $this->assertEquals(3, $options[CURLOPT_MAXREDIRS]);
    }

    public function testUserAgentConstant(): void
    {
        $this->assertStringContainsString('PHP Curl', Client::USER_AGENT);
        $this->assertStringContainsString('https://github.com/fastdlabs/http', Client::USER_AGENT);
    }

    public function testWithOption(): void
    {
        $newClient = $this->client->withOption(CURLOPT_TIMEOUT, 120);
        
        // 验证不可变性
        $this->assertNotSame($this->client, $newClient);
        $this->assertEquals(30, $this->client->getOptions()[CURLOPT_TIMEOUT]);
        $this->assertEquals(120, $newClient->getOptions()[CURLOPT_TIMEOUT]);
    }

    public function testWithMultipleOptions(): void
    {
        $client = $this->client
            ->withOption(CURLOPT_TIMEOUT, 60)
            ->withOption(CURLOPT_CONNECTTIMEOUT, 15)
            ->withOption(CURLOPT_MAXREDIRS, 5);
        
        $options = $client->getOptions();
        $this->assertEquals(60, $options[CURLOPT_TIMEOUT]);
        $this->assertEquals(15, $options[CURLOPT_CONNECTTIMEOUT]);
        $this->assertEquals(5, $options[CURLOPT_MAXREDIRS]);
    }

    public function testGetOptions(): void
    {
        $options = $this->client->getOptions();
        $this->assertIsArray($options);
        $this->assertArrayHasKey(CURLOPT_TIMEOUT, $options);
        $this->assertArrayHasKey(CURLOPT_USERAGENT, $options);
    }

    // ===== Cookie 管理测试 =====

    public function testWithCookie(): void
    {
        $cookie = Cookie::create('session_id', 'abc123');
        $newClient = $this->client->withCookie($cookie);
        
        // 验证不可变性
        $this->assertNotSame($this->client, $newClient);
        $this->assertEmpty($this->client->getCookies());
        $this->assertCount(1, $newClient->getCookies());
        $this->assertArrayHasKey('session_id', $newClient->getCookies());
    }

    public function testWithMultipleCookies(): void
    {
        $cookie1 = Cookie::create('session_id', 'abc123');
        $cookie2 = Cookie::create('user_id', '456');
        
        $client = $this->client
            ->withCookie($cookie1)
            ->withCookie($cookie2);
        
        $cookies = $client->getCookies();
        $this->assertCount(2, $cookies);
        $this->assertArrayHasKey('session_id', $cookies);
        $this->assertArrayHasKey('user_id', $cookies);
        $this->assertEquals('abc123', $cookies['session_id']->getValue());
        $this->assertEquals('456', $cookies['user_id']->getValue());
    }

    public function testGetCookies(): void
    {
        $this->assertIsArray($this->client->getCookies());
        $this->assertEmpty($this->client->getCookies());
    }

    // ===== requestBefore 方法测试 =====

    public function testRequestBeforeWithHeaders(): void
    {
        $request = new Request('GET', 'https://example.com');
        $options = [
            'headers' => [
                'X-Custom-Header' => 'custom-value',
                'Authorization' => 'Bearer token123'
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertTrue($modifiedRequest->hasHeader('X-Custom-Header'));
        $this->assertTrue($modifiedRequest->hasHeader('Authorization'));
        $this->assertEquals(['custom-value'], $modifiedRequest->getHeader('X-Custom-Header'));
        $this->assertEquals(['Bearer token123'], $modifiedRequest->getHeader('Authorization'));
    }

    public function testRequestBeforeWithMultipleHeaderValues(): void
    {
        $request = new Request('GET', 'https://example.com');
        $options = [
            'headers' => [
                'Accept' => ['application/json', 'text/html'],
                'Accept-Language' => ['zh-CN', 'en-US']
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertEquals(['application/json', 'text/html'], $modifiedRequest->getHeader('Accept'));
        $this->assertEquals(['zh-CN', 'en-US'], $modifiedRequest->getHeader('Accept-Language'));
    }

    public function testRequestBeforeWithCaseInsensitiveHeaders(): void
    {
        $request = new Request('GET', 'https://example.com');
        $request = $request->withHeader('content-type', 'text/plain');
        
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'AUTHORIZATION' => 'Bearer token'
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        // PSR-7 headers 不区分大小写
        $this->assertTrue($modifiedRequest->hasHeader('content-type'));
        $this->assertTrue($modifiedRequest->hasHeader('Content-Type'));
        $this->assertTrue($modifiedRequest->hasHeader('authorization'));
        $this->assertEquals(['application/json'], $modifiedRequest->getHeader('content-type'));
    }

    public function testRequestBeforeWithSpecialCharactersInHeaders(): void
    {
        $request = new Request('GET', 'https://example.com');
        $options = [
            'headers' => [
                'X-Custom-Data' => 'value with spaces',
                'X-Unicode' => '中文测试',
                'X-Special' => 'value!@#$%^&*()'
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertEquals(['value with spaces'], $modifiedRequest->getHeader('X-Custom-Data'));
        $this->assertEquals(['中文测试'], $modifiedRequest->getHeader('X-Unicode'));
        $this->assertEquals(['value!@#$%^&*()'], $modifiedRequest->getHeader('X-Special'));
    }

    public function testRequestBeforeWithQuery(): void
    {
        $request = new Request('GET', 'https://example.com/path');
        $options = [
            'query' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertStringContainsString('key1=value1', $modifiedRequest->getUri()->getQuery());
        $this->assertStringContainsString('key2=value2', $modifiedRequest->getUri()->getQuery());
    }

    public function testRequestBeforeWithBody(): void
    {
        $request = new Request('POST', 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/json');
        
        $options = [
            'body' => ['name' => 'John', 'age' => 30]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        // 重新获取 body 内容（因为 rewind 后可以再次读取）
        $modifiedRequest->getBody()->rewind();
        $bodyContent = $modifiedRequest->getBody()->getContents();
        
        $this->assertJson($bodyContent);
        $decoded = json_decode($bodyContent, true);
        $this->assertEquals('John', $decoded['name']);
        $this->assertEquals(30, $decoded['age']);
    }

    public function testRequestBeforeWithFormData(): void
    {
        $request = new Request('POST', 'https://example.com');
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        $options = [
            'body' => ['field1' => 'value1', 'field2' => 'value2']
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        $modifiedRequest->getBody()->rewind();
        $bodyContent = $modifiedRequest->getBody()->getContents();
        
        $this->assertEquals('field1=value1&field2=value2', $bodyContent);
    }

    public function testRequestBeforeWithTextPlain(): void
    {
        $request = new Request('POST', 'https://example.com');
        $request = $request->withHeader('Content-Type', 'text/plain');
        
        $options = [
            'body' => 'Plain text content'
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        $modifiedRequest->getBody()->rewind();
        $bodyContent = $modifiedRequest->getBody()->getContents();
        
        $this->assertEquals('Plain text content', $bodyContent);
    }

    public function testRequestBeforeWithMultipleOptions(): void
    {
        $request = new Request('POST', 'https://example.com/api');
        $request = $request->withHeader('Content-Type', 'application/json');
        
        $options = [
            'headers' => ['X-API-Key' => 'secret'],
            'query' => ['format' => 'json'],
            'body' => ['data' => 'test']
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertTrue($modifiedRequest->hasHeader('X-API-Key'));
        $this->assertStringContainsString('format=json', $modifiedRequest->getUri()->getQuery());
        $modifiedRequest->getBody()->rewind();
        $this->assertJson($modifiedRequest->getBody()->getContents());
    }

    // ===== send 方法测试 =====

    public function testSendWithOptions(): void
    {
        $request = new Request('GET', 'https://www.baidu.com/');
        $options = [
            'query' => ['wd' => 'test']
        ];
        
        $response = $this->client->send($request, $options);
        
        $this->assertInstanceOf(Text::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    // ===== sendRequest 方法测试（真实请求） =====

    public function testSendRequestGet(): void
    {
        $request = new Request('GET', 'https://www.baidu.com/');
        $response = $this->client->sendRequest($request);
        
        $this->assertInstanceOf(Text::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotEmpty($response->getContents());
    }

    public function testSendRequestPost(): void
    {
        // 使用 httpbin.org 测试 POST 请求
        $request = new Request('POST', 'https://httpbin.org/post');
        $request = $request->withHeader('Content-Type', 'application/json');
        $body = Stream::create(json_encode(['test' => 'data']));
        $request = $request->withBody($body);
        
        $response = $this->client->sendRequest($request);
        
        $this->assertInstanceOf(Json::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testSendRequestWithCustomHeaders(): void
    {
        $request = new Request('GET', 'https://httpbin.org/headers');
        $request = $request->withHeader('X-Custom-Header', 'test-value');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Json::class, $response);
    }

    // ===== 错误处理测试 =====

    public function testSendRequestTimeout(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessageMatches('/timeout/i');
        
        // 设置极短的超时时间
        $client = new Client([CURLOPT_TIMEOUT_MS => 1]);
        $request = new Request('GET', 'https://httpbin.org/delay/10');
        
        $client->sendRequest($request);
    }

    public function testSendRequestConnectionError(): void
    {
        // 测试连接错误，可能抛出 RequestException 或 ClientException
        $this->expectException(\Throwable::class);
        
        $request = new Request('GET', 'https://192.0.2.1:9999');
        $client = new Client([CURLOPT_CONNECTTIMEOUT => 1]);
        $client->sendRequest($request);
    }

    // ===== 响应类型检测测试 =====

    public function testResponseTypeJson(): void
    {
        $request = new Request('GET', 'https://httpbin.org/json');
        $response = $this->client->sendRequest($request);
        
        $this->assertInstanceOf(Json::class, $response);
    }

    public function testResponseTypeText(): void
    {
        $request = new Request('GET', 'https://httpbin.org/html');
        $response = $this->client->sendRequest($request);
        
        $this->assertInstanceOf(Text::class, $response);
    }

    // ===== Cookie 发送和接收测试 =====

    public function testSendRequestWithCookie(): void
    {
        $cookie = Cookie::create('test_cookie', 'test_value');
        $client = $this->client->withCookie($cookie);
        
        $request = new Request('GET', 'https://httpbin.org/cookies');
        $response = $client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Json::class, $response);
    }

    public function testReceiveCookieFromResponse(): void
    {
        $request = new Request('GET', 'https://httpbin.org/cookies/set?session=abc123');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 响应应该包含设置的 cookie
        $cookies = $response->getCookies();
        $this->assertNotEmpty($cookies);
    }

    // ===== 压缩内容处理测试 =====

    public function testHandleGzipCompression(): void
    {
        $request = new Request('GET', 'https://httpbin.org/gzip');
        $request = $request->withHeader('Accept-Encoding', 'gzip');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Json::class, $response);
    }

    // ===== HTTP 方法测试 =====

    public function testPutRequest(): void
    {
        $request = new Request('PUT', 'https://httpbin.org/put');
        $request = $request->withHeader('Content-Type', 'application/json');
        $body = Stream::create(json_encode(['update' => 'data']));
        $request = $request->withBody($body);
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testDeleteRequest(): void
    {
        $request = new Request('DELETE', 'https://httpbin.org/delete');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPatchRequest(): void
    {
        $request = new Request('PATCH', 'https://httpbin.org/patch');
        $request = $request->withHeader('Content-Type', 'application/json');
        $body = Stream::create(json_encode(['patch' => 'data']));
        $request = $request->withBody($body);
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    // ===== 重定向测试 =====

    public function testFollowRedirect(): void
    {
        $request = new Request('GET', 'https://httpbin.org/redirect/2');
        $response = $this->client->sendRequest($request);
        
        // 应该自动跟随重定向并返回最终响应
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMaxRedirects(): void
    {
        $this->expectException(ClientException::class);
        
        // 设置最大重定向次数为1，但请求需要2次重定向
        $client = $this->client->withOption(CURLOPT_MAXREDIRS, 1);
        $request = new Request('GET', 'https://httpbin.org/redirect/3');
        
        $client->sendRequest($request);
    }

    // ===== 状态码测试 =====

    public function testHandle404Response(): void
    {
        $request = new Request('GET', 'https://httpbin.org/status/404');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testHandle500Response(): void
    {
        $request = new Request('GET', 'https://httpbin.org/status/500');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(500, $response->getStatusCode());
    }

    // ===== 边界情况测试 =====

    public function testEmptyResponse(): void
    {
        $request = new Request('GET', 'https://httpbin.org/status/204');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testLargeResponseBody(): void
    {
        // 获取大量数据的响应
        $request = new Request('GET', 'https://httpbin.org/bytes/10240');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertGreaterThan(10000, strlen($response->getContents()));
    }

    public function testSpecialCharactersInUrl(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get?name=测试&value=特殊字符');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    // ===== Header 解析和处理测试 =====

    public function testResponseHeadersParsing(): void
    {
        $request = new Request('GET', 'https://httpbin.org/response-headers?X-Custom=test&X-Test=value');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('X-Custom'));
        $this->assertTrue($response->hasHeader('X-Test'));
    }

    public function testResponseMultiValueHeaders(): void
    {
        // httpbin 会在响应中设置 Set-Cookie
        $request = new Request('GET', 'https://httpbin.org/cookies/set?cookie1=value1&cookie2=value2');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证接收到的 cookies
        $cookies = $response->getCookies();
        $this->assertNotEmpty($cookies);
    }

    public function testResponseContentTypeHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/json');
        $response = $this->client->sendRequest($request);
        
        $this->assertTrue($response->hasHeader('Content-Type'));
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $contentType);
    }

    public function testResponseHeadersWithSemicolon(): void
    {
        // 测试包含分号的 header（如 Content-Type: text/html; charset=utf-8）
        $request = new Request('GET', 'https://httpbin.org/html');
        $response = $this->client->sendRequest($request);
        
        $this->assertTrue($response->hasHeader('Content-Type'));
        $contentType = $response->getHeader('Content-Type');
        $this->assertIsArray($contentType);
    }

    public function testRequestHeadersFormatting(): void
    {
        $request = new Request('GET', 'https://httpbin.org/headers');
        $request = $request
            ->withHeader('User-Agent', 'Custom-Agent/1.0')
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Encoding', 'gzip, deflate')
            ->withHeader('X-Request-ID', '12345');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Json::class, $response);
    }

    public function testAuthorizationHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/bearer');
        $request = $request->withHeader('Authorization', 'Bearer test-token-12345');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Json::class, $response);
    }

    public function testBasicAuthHeader(): void
    {
        $username = 'testuser';
        $password = 'testpass';
        $auth = base64_encode($username . ':' . $password);
        
        $request = new Request('GET', "https://httpbin.org/basic-auth/{$username}/{$password}");
        $request = $request->withHeader('Authorization', 'Basic ' . $auth);
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCustomHeadersInRequest(): void
    {
        $request = new Request('GET', 'https://httpbin.org/headers');
        $request = $request
            ->withHeader('X-API-Key', 'secret-key-123')
            ->withHeader('X-Request-Time', (string)time())
            ->withHeader('X-Client-Version', '1.0.0');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testHeaderOverriding(): void
    {
        $request = new Request('GET', 'https://httpbin.org/headers');
        $request = $request->withHeader('X-Test', 'original');
        
        $options = [
            'headers' => [
                'X-Test' => 'overridden'
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertEquals(['overridden'], $modifiedRequest->getHeader('X-Test'));
    }

    public function testMultipleHeaderValuesFormatting(): void
    {
        $request = new Request('GET', 'https://httpbin.org/headers');
        $request = $request->withHeader('Accept', ['application/json', 'application/xml', 'text/html']);
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testEmptyHeaderValue(): void
    {
        $request = new Request('GET', 'https://example.com');
        $options = [
            'headers' => [
                'X-Empty' => ''
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertTrue($modifiedRequest->hasHeader('X-Empty'));
        $this->assertEquals([''], $modifiedRequest->getHeader('X-Empty'));
    }

    public function testHeadersWithNumericValues(): void
    {
        $request = new Request('GET', 'https://example.com');
        $options = [
            'headers' => [
                'X-Request-ID' => '12345',
                'X-Timestamp' => (string)time()
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertTrue($modifiedRequest->hasHeader('X-Request-ID'));
        $this->assertTrue($modifiedRequest->hasHeader('X-Timestamp'));
    }

    public function testContentLengthHeader(): void
    {
        $body = json_encode(['test' => 'data']);
        $request = new Request('POST', 'https://httpbin.org/post');
        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Stream::create($body));
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAcceptEncodingHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/gzip');
        $request = $request->withHeader('Accept-Encoding', 'gzip, deflate, br');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCacheControlHeaders(): void
    {
        $request = new Request('GET', 'https://httpbin.org/cache/60');
        $request = $request->withHeader('Cache-Control', 'no-cache');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRefererHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/headers');
        $request = $request->withHeader('Referer', 'https://example.com/previous-page');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUserAgentHeaderCustomization(): void
    {
        $customUserAgent = 'MyCustomClient/2.0 (PHP)';
        $request = new Request('GET', 'https://httpbin.org/user-agent');
        $request = $request->withHeader('User-Agent', $customUserAgent);
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Json::class, $response);
    }

    public function testHeadersWithColonInValue(): void
    {
        $request = new Request('GET', 'https://example.com');
        $options = [
            'headers' => [
                'X-Timestamp' => date('Y-m-d H:i:s'),
                'X-URL' => 'https://example.com:8080/path'
            ]
        ];
        
        $modifiedRequest = $this->client->requestBefore($request, $options);
        
        $this->assertTrue($modifiedRequest->hasHeader('X-Timestamp'));
        $this->assertTrue($modifiedRequest->hasHeader('X-URL'));
    }

    // ===== 响应头校对测试 =====

    public function testResponseHeaderCaseInsensitive(): void
    {
        $request = new Request('GET', 'https://httpbin.org/response-headers?Content-Type=text/plain&content-length=100');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // PSR-7 响应头不区分大小写
        $this->assertTrue($response->hasHeader('content-type'));
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertTrue($response->hasHeader('CONTENT-TYPE'));
    }

    public function testResponseDateHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 所有 HTTP 响应应该包含 Date 头
        $this->assertTrue($response->hasHeader('Date'));
        $dateHeader = $response->getHeaderLine('Date');
        $this->assertNotEmpty($dateHeader);
    }

    public function testResponseServerHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 应该包含 Server 头
        if ($response->hasHeader('Server')) {
            $serverHeader = $response->getHeaderLine('Server');
            $this->assertNotEmpty($serverHeader);
        }
    }

    public function testResponseContentLengthHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/bytes/1024');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证 Content-Length 头
        if ($response->hasHeader('Content-Length')) {
            $contentLength = $response->getHeaderLine('Content-Length');
            $this->assertGreaterThan(0, (int)$contentLength);
        }
    }

    public function testResponseTransferEncodingHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/stream/10');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 检查是否有 Transfer-Encoding 头
        if ($response->hasHeader('Transfer-Encoding')) {
            $encoding = $response->getHeaderLine('Transfer-Encoding');
            $this->assertNotEmpty($encoding);
        }
    }

    public function testResponseConnectionHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证 Connection 头
        if ($response->hasHeader('Connection')) {
            $connection = $response->getHeaderLine('Connection');
            $this->assertContains(strtolower($connection), ['keep-alive', 'close']);
        }
    }

    public function testResponseContentEncodingHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/gzip');
        $request = $request->withHeader('Accept-Encoding', 'gzip');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 应该包含 Content-Encoding 头
        if ($response->hasHeader('Content-Encoding')) {
            $encoding = $response->getHeaderLine('Content-Encoding');
            $this->assertStringContainsString('gzip', strtolower($encoding));
        }
    }

    public function testResponseEtagHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/etag/test-etag-123');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证 ETag 头
        if ($response->hasHeader('ETag')) {
            $etag = $response->getHeaderLine('ETag');
            $this->assertNotEmpty($etag);
        }
    }

    public function testResponseCacheControlHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/cache/60');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证 Cache-Control 头
        if ($response->hasHeader('Cache-Control')) {
            $cacheControl = $response->getHeaderLine('Cache-Control');
            $this->assertNotEmpty($cacheControl);
        }
    }

    public function testResponseLocationHeaderOnRedirect(): void
    {
        // 禁用自动重定向来检查 Location 头
        $client = $this->client->withOption(CURLOPT_FOLLOWLOCATION, false);
        $request = new Request('GET', 'https://httpbin.org/redirect-to?url=https://httpbin.org/get');
        
        $response = $client->sendRequest($request);
        
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        // 重定向响应应该包含 Location 头
        $this->assertTrue($response->hasHeader('Location'));
        $location = $response->getHeaderLine('Location');
        $this->assertNotEmpty($location);
    }

    public function testResponseSetCookieHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/cookies/set?test=value123');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // Set-Cookie 应该被解析为 Cookie 对象
        $cookies = $response->getCookies();
        $this->assertNotEmpty($cookies);
        $this->assertArrayHasKey('test', $cookies);
    }

    public function testResponseMultipleSetCookieHeaders(): void
    {
        $request = new Request('GET', 'https://httpbin.org/cookies/set?cookie1=value1&cookie2=value2&cookie3=value3');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 多个 Set-Cookie 应该被正确解析
        $cookies = $response->getCookies();
        $this->assertGreaterThanOrEqual(3, count($cookies));
    }

    public function testResponseAccessControlHeaders(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $request = $request->withHeader('Origin', 'https://example.com');
        
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 检查 CORS 相关头
        if ($response->hasHeader('Access-Control-Allow-Origin')) {
            $allowOrigin = $response->getHeaderLine('Access-Control-Allow-Origin');
            $this->assertNotEmpty($allowOrigin);
        }
    }

    public function testResponseVaryHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 检查 Vary 头
        if ($response->hasHeader('Vary')) {
            $vary = $response->getHeaderLine('Vary');
            $this->assertNotEmpty($vary);
        }
    }

    public function testResponseCustomHeaders(): void
    {
        $request = new Request('GET', 'https://httpbin.org/response-headers?X-Custom-Header=custom-value&X-Another=test');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证自定义响应头
        $this->assertTrue($response->hasHeader('X-Custom-Header'));
        $this->assertEquals(['custom-value'], $response->getHeader('X-Custom-Header'));
        $this->assertTrue($response->hasHeader('X-Another'));
        $this->assertEquals(['test'], $response->getHeader('X-Another'));
    }

    public function testResponseHeaderWithMultipleValues(): void
    {
        // 测试响应头包含多个值（用逗号分隔）
        $request = new Request('GET', 'https://httpbin.org/response-headers?Accept=application/json,text/html');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        if ($response->hasHeader('Accept')) {
            $accept = $response->getHeader('Accept');
            $this->assertIsArray($accept);
        }
    }

    public function testResponseHeaderLineMethod(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 测试 getHeaderLine 方法
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertIsString($contentType);
        $this->assertNotEmpty($contentType);
    }

    public function testResponseGetHeadersMethod(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 测试 getHeaders 方法
        $headers = $response->getHeaders();
        $this->assertIsArray($headers);
        $this->assertNotEmpty($headers);
        $this->assertArrayHasKey('content-type', $headers);
    }

    public function testResponseHeaderWithEmptyValue(): void
    {
        $request = new Request('GET', 'https://httpbin.org/response-headers?X-Empty=');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证空值响应头
        if ($response->hasHeader('X-Empty')) {
            $empty = $response->getHeader('X-Empty');
            $this->assertIsArray($empty);
        }
    }

    public function testResponseHeaderWithSpecialCharacters(): void
    {
        $request = new Request('GET', 'https://httpbin.org/response-headers?X-Special=value!@%23$%25');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证包含特殊字符的响应头
        if ($response->hasHeader('X-Special')) {
            $special = $response->getHeaderLine('X-Special');
            $this->assertNotEmpty($special);
        }
    }

    public function testResponseContentTypeWithCharset(): void
    {
        $request = new Request('GET', 'https://httpbin.org/html');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // Content-Type 应该包含 charset
        $contentType = $response->getHeaderLine('Content-Type');
        $this->assertStringContainsString('text/html', strtolower($contentType));
    }

    public function testResponseHeadersPersistence(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证多次访问同一 header 返回相同结果
        $contentType1 = $response->getHeaderLine('Content-Type');
        $contentType2 = $response->getHeaderLine('Content-Type');
        $this->assertEquals($contentType1, $contentType2);
    }

    public function testResponseHeadersNotFoundBehavior(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 不存在的 header 应该返回空数组
        $this->assertFalse($response->hasHeader('X-Nonexistent-Header'));
        $this->assertEquals([], $response->getHeader('X-Nonexistent-Header'));
        $this->assertEquals('', $response->getHeaderLine('X-Nonexistent-Header'));
    }

    public function testResponseXPoweredByHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 检查是否有 X-Powered-By 头
        if ($response->hasHeader('X-Powered-By')) {
            $poweredBy = $response->getHeaderLine('X-Powered-By');
            $this->assertNotEmpty($poweredBy);
        }
    }

    public function testResponseStrictTransportSecurityHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/get');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 检查 HSTS 头
        if ($response->hasHeader('Strict-Transport-Security')) {
            $hsts = $response->getHeaderLine('Strict-Transport-Security');
            $this->assertNotEmpty($hsts);
        }
    }

    public function testResponseContentDispositionHeader(): void
    {
        // 测试下载文件时的 Content-Disposition 头
        $request = new Request('GET', 'https://httpbin.org/response-headers?Content-Disposition=attachment;filename=test.txt');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        if ($response->hasHeader('Content-Disposition')) {
            $disposition = $response->getHeaderLine('Content-Disposition');
            $this->assertStringContainsString('attachment', $disposition);
        }
    }

    public function testResponseAgeHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/cache/60');
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 检查 Age 头（缓存年龄）
        if ($response->hasHeader('Age')) {
            $age = $response->getHeaderLine('Age');
            $this->assertIsNumeric($age);
        }
    }

    public function testResponseLastModifiedHeader(): void
    {
        $request = new Request('GET', 'https://httpbin.org/response-headers?Last-Modified=' . urlencode(gmdate('D, d M Y H:i:s') . ' GMT'));
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证 Last-Modified 头
        if ($response->hasHeader('Last-Modified')) {
            $lastModified = $response->getHeaderLine('Last-Modified');
            $this->assertNotEmpty($lastModified);
        }
    }

    public function testResponseExpiresHeader(): void
    {
        $expiresDate = gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT';
        $request = new Request('GET', 'https://httpbin.org/response-headers?Expires=' . urlencode($expiresDate));
        $response = $this->client->sendRequest($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        // 验证 Expires 头
        if ($response->hasHeader('Expires')) {
            $expires = $response->getHeaderLine('Expires');
            $this->assertNotEmpty($expires);
        }
    }
}