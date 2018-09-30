<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/14
 * Time: 上午10:18
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace Tests;

use DateTime;
use FastD\Http\Cookie;
use FastD\Http\Response;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @var Response
     */
    protected $response;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testResponseContent()
    {
        $this->response->withContent('hello world');
        echo $this->response->getBody();
        $this->expectOutputString('hello world');
    }

    public function testResponseHeaders()
    {
        $this->response->withHeader('age', 11);
        $this->assertEquals(11, $this->response->getHeaderLine('age'));
    }

    public function outputResponse(Response $response)
    {
        echo PHP_EOL;
        echo $response;
    }

    public function testResponseContentType()
    {
        $this->response->withContentType('text/png');
        $this->outputResponse($this->response);
    }

    public function testResponseCacheControl()
    {
        $this->response->withCacheControl('public');
        $this->outputResponse($this->response);
        $this->response->withCacheControl('no-cache');
        $this->outputResponse($this->response);
    }

    public function testResponseExpire()
    {
        $this->response->withExpires(new DateTime('2018-12-31'));
        $this->outputResponse($this->response);
        $this->response->withCacheControl('public');
        $this->response->withMaxAge(0);
        $this->outputResponse($this->response);
    }

    public function testResponseMofify()
    {
        $this->response->withLastModified(new DateTime());
        $this->outputResponse($this->response);
        $this->response->withNotModified();
        $this->outputResponse($this->response);
        $this->assertEquals(304, $this->response->getStatusCode());
    }

    public function testInvalidStatusCode()
    {
        $this->assertFalse($this->response->isInvalidStatusCode());
        echo $this->response->getReasonPhrase();
    }

    public function testResponseCookie()
    {
        $this->response->withCookieParams([
            'foo' => Cookie::normalizer('foo', 'bar')
        ]);
        $this->response->withCookie('age', 11);
        $this->outputResponse($this->response);
    }
}
