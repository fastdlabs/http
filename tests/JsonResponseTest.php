<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */


use FastD\Http\JsonResponse;

class JsonResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testResponseJson()
    {
        $response = new JsonResponse([
            'foo' => 'bar',
        ]);

        $this->assertEquals($response->getContentType(), 'application/json; charset=UTF-8');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->isOk());
        $this->assertTrue($response->isSuccessful());
    }

    public function testJsonResponsePrint()
    {
        $response = new JsonResponse([
            'foo' => 'bar',
        ]);

        $body = $response->getBody();
        $this->assertEquals(json_decode($body, true), [
            'foo' => 'bar'
        ]);
    }
}
