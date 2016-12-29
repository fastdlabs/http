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

use FastD\Http\Response;
use Psr\Http\Message\ResponseInterface;

class ResponseTest extends \PHPUnit_Framework_TestCase
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

    }
}