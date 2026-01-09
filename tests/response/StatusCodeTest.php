<?php

declare(strict_types=1);

use FastD\Http\Response\StatusCode;
use PHPUnit\Framework\TestCase;

/**
 * StatusCode接口单元测试
 */
class StatusCodeTest extends TestCase
{
    /**
     * 测试HTTP继续状态码常量
     */
    public function testHttpContinueConstant()
    {
        $this->assertEquals(100, StatusCode::HTTP_CONTINUE);
    }

    /**
     * 测试HTTP成功状态码常量
     */
    public function testHttpSuccessConstants()
    {
        $this->assertEquals(200, StatusCode::HTTP_OK);
        $this->assertEquals(201, StatusCode::HTTP_CREATED);
        $this->assertEquals(202, StatusCode::HTTP_ACCEPTED);
        $this->assertEquals(204, StatusCode::HTTP_NO_CONTENT);
    }

    /**
     * 测试HTTP重定向状态码常量
     */
    public function testHttpRedirectionConstants()
    {
        $this->assertEquals(301, StatusCode::HTTP_MOVED_PERMANENTLY);
        $this->assertEquals(302, StatusCode::HTTP_FOUND);
        $this->assertEquals(303, StatusCode::HTTP_SEE_OTHER);
        $this->assertEquals(304, StatusCode::HTTP_NOT_MODIFIED);
        $this->assertEquals(307, StatusCode::HTTP_TEMPORARY_REDIRECT);
        $this->assertEquals(308, StatusCode::HTTP_PERMANENTLY_REDIRECT);
    }

    /**
     * 测试HTTP客户端错误状态码常量
     */
    public function testHttpClientErrorConstants()
    {
        $this->assertEquals(400, StatusCode::HTTP_BAD_REQUEST);
        $this->assertEquals(401, StatusCode::HTTP_UNAUTHORIZED);
        $this->assertEquals(403, StatusCode::HTTP_FORBIDDEN);
        $this->assertEquals(404, StatusCode::HTTP_NOT_FOUND);
        $this->assertEquals(405, StatusCode::HTTP_METHOD_NOT_ALLOWED);
        $this->assertEquals(418, StatusCode::HTTP_I_AM_A_TEAPOT); // 经典的茶壶状态码
    }

    /**
     * 测试HTTP服务器错误状态码常量
     */
    public function testHttpServerErrorConstants()
    {
        $this->assertEquals(500, StatusCode::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(501, StatusCode::HTTP_NOT_IMPLEMENTED);
        $this->assertEquals(502, StatusCode::HTTP_BAD_GATEWAY);
        $this->assertEquals(503, StatusCode::HTTP_SERVICE_UNAVAILABLE);
        $this->assertEquals(504, StatusCode::HTTP_GATEWAY_TIMEOUT);
    }

    /**
     * 测试状态文本映射表包含关键状态码
     */
    public function testStatusTextContainsKeyCodes()
    {
        $statusText = StatusCode::PHRASES;
        
        $this->assertArrayHasKey(200, $statusText);
        $this->assertArrayHasKey(404, $statusText);
        $this->assertArrayHasKey(500, $statusText);
        $this->assertArrayHasKey(301, $statusText);
        $this->assertArrayHasKey(403, $statusText);
        
        $this->assertEquals('OK', $statusText[200]);
        $this->assertEquals('Not Found', $statusText[404]);
        $this->assertEquals('Internal Server Error', $statusText[500]);
        $this->assertEquals('Moved Permanently', $statusText[301]);
        $this->assertEquals('Forbidden', $statusText[403]);
    }

    /**
     * 测试状态文本映射表包含所有常见的状态码
     */
    public function testStatusTextContainsCommonCodes()
    {
        $statusText = StatusCode::PHRASES;
        
        $commonCodes = [
            100 => 'Continue',
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            301 => 'Moved Permanently',
            302 => 'Found',
            304 => 'Not Modified',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            418 => 'I\'m a teapot', // RFC2324
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable'
        ];
        
        foreach ($commonCodes as $code => $text) {
            $this->assertArrayHasKey($code, $statusText, "Status code $code should exist in STATUS_TEXT");
            $this->assertEquals($text, $statusText[$code], "Status text for code $code should be '$text'");
        }
    }

    /**
     * 测试状态文本映射表的完整性
     */
    public function testStatusTextHasExpectedSize()
    {
        $statusText = StatusCode::PHRASES;
        // 至少应包含几十个标准HTTP状态码
        $this->assertGreaterThan(40, count($statusText), "STATUS_TEXT should contain many HTTP status codes");
    }

    /**
     * 验证接口可以被其他类实现
     */
    public function testInterfaceImplementation()
    {
        $this->assertTrue(interface_exists(StatusCode::class));
    }

    /**
     * 测试一些特殊的RFC定义的状态码
     */
    public function testSpecialRfcCodes()
    {
        $statusText = StatusCode::PHRASES;
        
        // 测试一些特殊的RFC定义的状态码
        $this->assertArrayHasKey(422, $statusText); // Unprocessable Entity (RFC4918)
        $this->assertArrayHasKey(429, $statusText); // Too Many Requests (RFC6585)
        $this->assertArrayHasKey(428, $statusText); // Precondition Required (RFC6585)
        $this->assertArrayHasKey(431, $statusText); // Request Header Fields Too Large (RFC6585)
        
        $this->assertEquals('Unprocessable Entity', $statusText[422]);
        $this->assertEquals('Too Many Requests', $statusText[429]);
        $this->assertEquals('Precondition Required', $statusText[428]);
        $this->assertEquals('Request Header Fields Too Large', $statusText[431]);
    }

    /**
     * 测试状态码范围分类
     */
    public function testStatusCodeRanges()
    {
        // 测试各种状态码范围
        $this->assertEquals(100, StatusCode::HTTP_CONTINUE); // 1xx
        $this->assertEquals(200, StatusCode::HTTP_OK);       // 2xx
        $this->assertEquals(300, StatusCode::HTTP_MULTIPLE_CHOICES); // 3xx
        $this->assertEquals(400, StatusCode::HTTP_BAD_REQUEST);      // 4xx
        $this->assertEquals(500, StatusCode::HTTP_INTERNAL_SERVER_ERROR); // 5xx
    }
}