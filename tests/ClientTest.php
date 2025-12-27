<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Client;
use FastD\Http\Request\Request;
use FastD\Http\Response\Response;
use FastD\Http\Exception\HttpException;
use FastD\Http\Response\StatusCodeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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

    public function testUserAgentConstant(): void
    {
        $this->assertStringContainsString('PHP Curl', Client::USER_AGENT);
        $this->assertStringContainsString('https://github.com/fastdlabs/http', Client::USER_AGENT);
    }

    public function testPatchRequestWithBody(): void
    {
        $request = new Request('GET', 'https://baidu.com/');
        $payload = [
            'query' => ['key' => 'value']
        ];

        $response = $this->client->request($request, $payload);
        $this->assertEquals(StatusCodeInterface::HTTP_MOVED_PERMANENTLY, $response->getStatusCode());
    }
}