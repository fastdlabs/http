<?php
use FastD\Http\Response;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    protected $response;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testBaseResponse()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testStatusCodeMutatorReturnsCloneWithChanges()
    {
        $response = $this->response->withStatus(400);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testResponseHeaderCache()
    {
        $this->response->setCacheControl('public');

        $header = $this->response->getHeaderBag();

        $this->assertEquals(
            'Content-Type: text/html; charset=UTF-8' . "\r\n" .
            'Cache-Control: public' . "\r\n"
            , (string)$header
        );
    }

    public function testResponseContents()
    {
        $this->response->withContent('test');

        $this->assertEquals('test', $this->response->getBody()->getContents());
    }
}
