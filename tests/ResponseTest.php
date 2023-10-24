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

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Response
     */
    protected $response;

    protected function setUp(): void
    {
        $this->response = new Response();
    }

    public function testVersion()
    {
        $this->assertEquals('1.1', $this->response->getProtocolVersion());
    }

    public function testResponseContent()
    {
        $this->response->withContent('hello world');
        $this->assertEquals('hello world', $this->response->getBody());
        $this->assertTrue($this->response->isOk());
    }

    public function testResponseHeaders()
    {
        $this->response->withHeader('foo', 'bar');
        $this->assertEquals('bar', $this->response->getHeaderLine('foo'));
    }

    public function testResponseContentType()
    {
        $this->response->withContentType('text/png');
        $this->assertEquals('text/png', $this->response->getContentType());
        $this->response->withContentType('text/jpeg');
        $this->assertEquals('text/jpeg', $this->response->getContentType());
    }

    public function testResponseCacheControl()
    {
        $this->response->withCacheControl('public');
        $this->assertEquals('public', $this->response->getCacheControl());
        $this->response->withCacheControl('no-cache');
        $this->assertEquals('no-cache', $this->response->getCacheControl());
        $this->response->withMaxAge(3600);
        $this->assertEquals('no-cache, max-age=3600', $this->response->getCacheControl());
        $this->response->withSharedMaxAge(100);
        $this->assertEquals('public, s-maxage=100', $this->response->getCacheControl());
    }

    public function testResponseExpire()
    {
        $this->response->withExpires(new DateTime('2024-12-31'));
        $this->response->withCacheControl('public');
        $this->response->withMaxAge(0);
        $this->assertEquals(0, $this->response->getMaxAge());
    }

    public function testETag()
    {
        $this->assertEmpty($this->response->getETag());
        $this->response->withETag(md5('foo'));
        $this->assertNotEmpty($this->response->getETag());
        $this->assertEquals(md5('foo'), $this->response->getETag());
    }

    public function testResponseModify()
    {
        $this->response->withLastModified(new DateTime());
        $this->assertEquals((new DateTime())->format('D, d M Y H:i:s') . ' GMT', $this->response->getLastModified());
        $this->response->withNotModified();
        $this->assertEquals(304, $this->response->getStatusCode());
        $this->assertEquals(Response::$statusTexts[304], $this->response->getReasonPhrase());
    }

    public function testInvalidStatusCode()
    {
        $this->assertFalse($this->response->isInvalidStatusCode());
        $this->assertEquals('OK', $this->response->getReasonPhrase());
    }

    public function testResponseCookie()
    {
        $this->response->withCookies([
            'foo' => Cookie::normalizer('foo', 'bar')
        ]);
        $this->assertCount(1, $this->response->getCookies());
        $this->response->withCookie('age', 11);
        $this->assertCount(2, $this->response->getCookies());
    }
}
