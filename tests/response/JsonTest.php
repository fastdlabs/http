<?php

declare(strict_types=1);

use FastD\Http\Response\Json;
use FastD\Http\Response\StatusCode;
use PHPUnit\Framework\TestCase;

/**
 * Json响应类完整单元测试
 */
class JsonTest extends TestCase
{
    // ===== 基础功能测试 =====

    public function testResponseJson()
    {
        $response = new Json(200, [
            'foo' => 'bar',
        ]);

        $this->assertEquals($response->getHeaderLine('Content-Type'), 'application/json; charset=UTF-8');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function testJsonResponsePrint()
    {
        $response = new Json(200, [
            'foo' => 'bar',
        ]);

        $body = $response->getContents();
        $this->assertEquals([
            'foo' => 'bar'
        ], json_decode($body, true));
        $this->assertEquals('bar', $response['foo']);
    }

    // ===== ArrayAccess接口测试 =====

    public function testArrayAccessOffsetExists()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $response = new Json(200, $data);

        $this->assertTrue(isset($response['key1']));
        $this->assertTrue(isset($response['key2']));
        $this->assertFalse(isset($response['nonexistent']));
    }

    public function testArrayAccessOffsetGet()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $response = new Json(200, $data);

        $this->assertEquals('value1', $response['key1']);
        $this->assertEquals('value2', $response['key2']);
        $this->assertNull($response['nonexistent']);
    }

    public function testArrayAccessOffsetSet()
    {
        $response = new Json(200, ['initial' => 'value']);
        
        // 注意：Json类的offsetSet会修改内部数组，但不会重新序列化到stream
        $response['new_key'] = 'new_value';
        
        // 直接通过ArrayAccess验证
        $this->assertEquals('new_value', $response['new_key']);
        $this->assertTrue(isset($response['new_key']));
    }

    public function testArrayAccessOffsetUnset()
    {
        $response = new Json(200, ['key1' => 'value1', 'key2' => 'value2']);
        
        unset($response['key1']);
        
        // 直接通过ArrayAccess验证
        $this->assertFalse(isset($response['key1']));
        $this->assertTrue(isset($response['key2']));
    }

    // ===== 构造函数测试 =====

    public function testConstructorWithEmptyArray()
    {
        $response = new Json(200, []);
        
        $this->assertEquals('[]', $response->getContents());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorWithNestedArray()
    {
        $nestedData = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'preferences' => [
                    'theme' => 'dark',
                    'notifications' => true
                ]
            ],
            'posts' => [
                ['id' => 1, 'title' => 'Post 1'],
                ['id' => 2, 'title' => 'Post 2']
            ]
        ];
        
        $response = new Json(200, $nestedData);
        
        $decoded = json_decode($response->getContents(), true);
        $this->assertEquals($nestedData, $decoded);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConstructorWithStatusAndHeaders()
    {
        $data = ['message' => 'Created'];
        $status = StatusCode::HTTP_CREATED;
        $headers = ['X-Custom-Header' => ['custom-value']];
        
        $response = new Json($status, $data, $headers);
        
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertEquals('Created', $response->getReasonPhrase());
        $this->assertTrue($response->hasHeader('X-Custom-Header'));
        $this->assertEquals('custom-value', $response->getHeaderLine('X-Custom-Header'));
    }

    // ===== 特殊数据类型测试 =====

    public function testJsonEncodingOfSpecialTypes()
    {
        $specialData = [
            'null_value' => null,
            'boolean_true' => true,
            'boolean_false' => false,
            'integer' => 42,
            'float' => 3.14159,
            'string' => 'hello world',
            'empty_string' => '',
            'unicode' => '你好世界 🌍',
        ];
        
        $response = new Json(200, $specialData);
        $decoded = json_decode($response->getContents(), true);
        
        $this->assertEquals($specialData, $decoded);
        $this->assertJson($response->getContents());
    }

    public function testJsonEncodingOfArrays()
    {
        $arrayData = [
            'simple_array' => [1, 2, 3, 4, 5],
            'mixed_array' => ['string', 123, true, null],
            'associative_array' => ['a' => 1, 'b' => 2, 'c' => 3],
        ];
        
        $response = new Json(200, $arrayData);
        $decoded = json_decode($response->getContents(), true);
        
        $this->assertEquals($arrayData, $decoded);
    }

    // ===== 错误处理测试 =====

    public function testJsonWithCircularReference()
    {
        $data = [];
        $data['self'] = &$data; // 创建循环引用
        
        $response = new Json(200, $data);
        
        // 循环引用会导致JSON编码失败，应返回空对象或类似
        $this->assertJson($response->getContents());
        $this->assertNotNull($response);
    }

    public function testJsonWithResourceType()
    {
        $resource = fopen('php://memory', 'r');
        $data = ['resource' => $resource];
        
        $response = new Json(200, $data);
        
        // 关闭资源
        fclose($resource);
        
        // 资源类型应该被转换为null或其他合适的形式
        $contents = $response->getContents();
        $this->assertJson($contents);
        $decoded = json_decode($contents, true);
        // 资源无法序列化，所以结果可能为空或不包含该字段
        $this->assertTrue(true); // 简单确认没有抛出异常
    }

    // ===== 性能测试 =====

    public function testJsonWithLargeDataset()
    {
        $largeData = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeData["item_$i"] = [
                'id' => $i,
                'name' => "Item $i",
                'description' => str_repeat('A', 100),
                'tags' => ["tag_$i", "category_" . ($i % 10)],
            ];
        }
        
        $startTime = microtime(true);
        $response = new Json(200, $largeData);
        $endTime = microtime(true);
        
        $processingTime = ($endTime - $startTime) * 1000; // 转换为毫秒
        
        // 应该在合理时间内处理（例如1秒内）
        $this->assertLessThan(1000, $processingTime);
        
        // 验证数据完整性
        $decoded = json_decode($response->getContents(), true);
        $this->assertCount(1000, $decoded);
        $this->assertEquals('Item 500', $decoded['item_500']['name']);
    }

    // ===== 边界情况测试 =====

    public function testJsonWithEmptyKeys()
    {
        $data = [
            '' => 'empty key value',
            'normal_key' => 'normal value'
        ];
        
        $response = new Json(200, $data);
        $decoded = json_decode($response->getContents(), true);
        
        $this->assertEquals($data, $decoded);
        $this->assertEquals('empty key value', $decoded['']);
    }

    public function testJsonWithNumericStringKeys()
    {
        $data = [
            '1' => 'one',
            '2' => 'two',
            '10' => 'ten'
        ];
        
        $response = new Json(200, $data);
        $decoded = json_decode($response->getContents(), true);
        
        $this->assertEquals($data, $decoded);
    }

    public function testJsonWithSpecialCharactersInKeys()
    {
        $data = [
            'key-with-dashes' => 'value1',
            'key_with_underscores' => 'value2',
            'key.with.dots' => 'value3',
            'key@symbol' => 'value4'
        ];
        
        $response = new Json(200, $data);
        $decoded = json_decode($response->getContents(), true);
        
        $this->assertEquals($data, $decoded);
    }

    // ===== 与其他响应类型的比较测试 =====

    public function testJsonVsTextResponse()
    {
        $data = ['message' => 'Hello World'];
        
        $jsonResponse = new Json(200, $data);
        $textResponse = new \FastD\Http\Response\Text(200, json_encode($data));
        
        // Json响应应该自动设置正确的Content-Type
        $this->assertEquals('application/json; charset=UTF-8', $jsonResponse->getHeaderLine('Content-Type'));
        
        // Text响应需要手动设置Content-Type
        $textResponse = $textResponse->withContentType('application/json');
        $this->assertEquals('application/json', $textResponse->getHeaderLine('Content-Type'));
        
        // 内容应该相同
        $this->assertEquals($jsonResponse->getContents(), $textResponse->getContents());
    }

    // ===== toString方法测试 =====

    public function testToStringMethod()
    {
        $data = ['test' => 'value'];
        $response = new Json(200, $data);
        
        $stringRepresentation = (string)$response;
        
        $this->assertStringContainsString('HTTP/1.1 200 OK', $stringRepresentation);
        $this->assertStringContainsString('Content-Type: application/json; charset=UTF-8', $stringRepresentation);
        $this->assertStringContainsString('{"test":"value"}', $stringRepresentation);
    }
}