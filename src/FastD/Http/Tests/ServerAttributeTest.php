<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/14
 * Time: 上午9:42
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Protocol\Http\Tests;

use FastD\Http\Attribute\ServerAttribute;

class ServerAttributeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServerAttribute
     */
    protected $server;

    public function setUp()
    {
        $this->server = new ServerAttribute([
            'SCRIPT_NAME'       => '/me/fastd/component/http/examples/base.php',
            'REQUEST_URI'       => '/me/fastd/component/http/examples/base.php/welcome?query=test',
            'PHP_SELF'          => '/me/fastd/component/http/examples/base.php',
            'REQUEST_SCHEME'    => 'http',
            'SERVER_NAME'       => 'localhost',
            'HTTP_HOST'         => 'localhost',
        ]);
    }

    public function testSchema()
    {
        $this->assertEquals('http', $this->server->getScheme());
    }

    public function testHost()
    {
        $this->assertEquals('localhost', $this->server->getHttpAndHost());

        $this->assertEquals('http://localhost', $this->server->getScheme() . '://' . $this->server->getHttpAndHost());
    }

    public function testRequestUri()
    {
        $this->assertEquals('/me/fastd/component/http/examples/base.php/welcome?query=test', $this->server->getRequestUri());
    }

    public function testBaseUrl()
    {
        $this->assertEquals('/me/fastd/component/http/examples/base.php', $this->server->getBaseUrl());
    }

    public function testRootPath()
    {
        $this->assertEquals('/me/fastd/component/http/examples', $this->server->getRootPath());
    }

    public function testPathInfo()
    {
        $this->assertEquals('/welcome', $this->server->getPathInfo());

        $server = new ServerAttribute([
            'SCRIPT_NAME'       => '/me/fastd/component/http/examples/base.php',
            'REQUEST_URI'       => '/me/fastd/component/http/examples/base.php?query=test',
            'PHP_SELF'          => '/me/fastd/component/http/examples/base.php',
            'REQUEST_SCHEME'    => 'http',
            'SERVER_NAME'       => 'localhost',
            'HTTP_HOST'         => 'localhost',
        ]);

        $this->assertEquals('/', $server->getPathInfo());

        $server = new ServerAttribute([
            'SCRIPT_NAME'       => '/me/fastd/component/http/examples/base.php',
            'REQUEST_URI'       => '/me/fastd/component/http/examples/base.php',
            'PHP_SELF'          => '/me/fastd/component/http/examples/base.php',
            'REQUEST_SCHEME'    => 'http',
            'SERVER_NAME'       => 'localhost',
            'HTTP_HOST'         => 'localhost',
        ]);

        $this->assertEquals('/', $server->getPathInfo());
    }
}