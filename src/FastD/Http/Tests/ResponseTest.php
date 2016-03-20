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

namespace FastD\Protocol\Http\Tests;

use FastD\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testResponseContent()
    {
        $response = new Response('demo');
        $this->assertEquals('demo', $response->getContent());
    }

    public function testErrorResponse()
    {
        $response = new Response('demo', 403);
        $this->assertEquals($response->getStatusCode(), 403);
    }
}