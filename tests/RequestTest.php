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

    public function testRequestTarget()
    {
        $request = new Request('GET', 'http://example.com');
    }
}
