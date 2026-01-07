<?php

namespace response;

use FastD\Http\Response\Json;

class JsonResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testResponseJson()
    {
        $response = new Json([
            'foo' => 'bar',
        ]);

        $this->assertEquals($response->getHeaderLine('Content-Type'), 'application/json; charset=UTF-8');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isOk());
        $this->assertTrue($response->isSuccessful());
    }

    public function testJsonResponsePrint()
    {
        $response = new Json([
            'foo' => 'bar',
        ]);

        $body = $response->getBody();
        $this->assertEquals([
            'foo' => 'bar'
        ], json_decode($body, true));
        $this->assertEquals('bar', $response['foo']);
    }
}
