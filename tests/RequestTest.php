<?php
use FastD\Http\Request;

/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testBaseRequest()
    {
        $request = new Request('https://api.github.com/');

        $request->setReferrer('http://example.com/');

        $response = $request->send();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('HTTP/1.1 200 OK', sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()));
    }

    public function testGetRequestMethod()
    {
        $request = new Request('https://api.github.com/');

        $request->withMethod('POST');

        $response = $request->send();

        $this->assertEquals(404, $response->getStatusCode());
    }
}
