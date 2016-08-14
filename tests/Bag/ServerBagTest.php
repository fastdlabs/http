<?php
use FastD\Http\Bag\ServerBag;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class ServerBagTest extends PHPUnit_Framework_TestCase
{
    protected $_server = [
        'SERVER_NAME' => 'localhost',
        'SERVER_ADDR' => '::1',
        'SERVER_PORT' => '80',
        'REMOTE_ADDR' => '::1',
        'DOCUMENT_ROOT' => '/Users/janhuang/Documents/htdocs',
        'REQUEST_SCHEME' => 'http',
        'CONTEXT_DOCUMENT_ROOT' => '/Users/janhuang/Documents/htdocs',
        'SCRIPT_FILENAME' => '/Users/janhuang/Documents/htdocs/me/fastd/library/http/examples/server.php',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/me/fastd/library/http/examples/server.php',
        'SCRIPT_NAME' => '/me/fastd/library/http/examples/server.php',
        'PHP_SELF' => '/me/fastd/library/http/examples/server.php',
    ];

    protected function changePathInfo($pathInfo)
    {
        $_server = $this->_server;

        $_server['REQUEST_URI'] .= $pathInfo;
        $_server['PHP_SELF'] .= $pathInfo;

        return $_server;
    }

    public function testPathInfo()
    {
        $serverBag = new ServerBag($this->_server);

        $this->assertEquals('/', $serverBag->getPathInfo());

        $serverBag = new ServerBag($this->changePathInfo('/test'));

        $this->assertEquals('/test', $serverBag->getPathInfo());
    }

    public function testRequestUri()
    {
        $serverBag = new ServerBag($this->_server);

        $this->assertEquals('/me/fastd/library/http/examples/server.php', $serverBag->getRequestUri());
    }

    public function testBaseUri()
    {
        $serverBag = new ServerBag($this->_server);

        $this->assertEquals('/me/fastd/library/http/examples/server.php', $serverBag->getBaseUrl());
    }

    public function testMethod()
    {
        $serverBag = new ServerBag($this->_server);

        $this->assertEquals('GET', $serverBag->getMethod());

        $this->assertTrue($serverBag->isMethod('get'));
        $this->assertTrue($serverBag->isMethod('GET'));

        $this->_server['REQUEST_METHOD'] = 'POST';

        $serverBag = new ServerBag($this->_server);

        $this->assertEquals('POST', $serverBag->getMethod());

        $this->assertTrue($serverBag->isMethod('post'));
        $this->assertTrue($serverBag->isMethod('POST'));
    }

    public function testUrlInfo()
    {
        $serverBag = new ServerBag($this->_server);

        $this->assertEquals('localhost', $serverBag->getHost());

        $this->assertFalse($serverBag->isSecure());

        $this->assertEquals($serverBag->getScheme(), 'http');

        $this->assertEquals($serverBag->getPort(), 80);
    }
}
