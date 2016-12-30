<?php
use FastD\Http\Request;
use FastD\Http\Uri;

/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequestUri()
    {
        $request = new Request('GET', 'http://example.com');

        $this->assertEquals($request->getUri()->getHost(), 'example.com');
        $this->assertNull($request->getUri()->getPort());
        $this->assertEquals('/', $request->getUri()->getPath());
        $this->assertEquals($request->getRequestTarget(), $request->getUri()->getPath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRequestUri()
    {
        new Request('GET', '///');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestMethod()
    {
        $request = new Request('GET', 'http://example.com');
        $this->assertEquals('GET', $request->getMethod());
        // Test invalid method
        $request->withMethod('ABC');
    }

    public function server()
    {
        $uri = new Uri('http://www.weather.com.cn/data/cityinfo/101010100.html');

        return new Request('GET', (string) $uri);
    }

    public function testRequestTarget()
    {
        $request = $this->server();
        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
