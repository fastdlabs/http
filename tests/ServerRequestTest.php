<?php
use FastD\Http\ServerRequest;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class ServerRequestTest extends PHPUnit_Framework_TestCase
{
    /***
     * @var ServerRequest
     */
    protected $serverRequest;

    public function setUp()
    {
        $this->serverRequest = new ServerRequest();
    }

    public function testServerParamsAreEmptyByDefault()
    {
        $this->assertEmpty($this->serverRequest->getServerParams());
    }

}
