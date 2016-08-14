<?php
use FastD\Http\Request;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    public function setUp()
    {
        $this->request = new Request();
    }

    public function testRequest()
    {
        $this->request->withMethod('GET');

        $this->assertEquals('GET', $this->request->getMethod());

        $this->request->withMethod('POST');

        $this->assertEquals('POST', $this->request->getMethod());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidMethod()
    {
        new Request('ABC', 'http://www.baidu.com');
    }
}
