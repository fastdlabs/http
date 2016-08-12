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
        'HTTP_HOST' => 'localhost',
        'HTTP_CONNECTION' => 'keep-alive',
        'HTTP_CACHE_CONTROL' => 'max-age=0',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36',
        'HTTP_REFERER' => 'http://localhost/me/fastd/library/http/examples/',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
        'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
        'SERVER_NAME' => 'localhost',
        'SERVER_ADDR' => '::1',
        'SERVER_PORT' => '80',
        'REMOTE_ADDR' => '::1',
        'DOCUMENT_ROOT' => '/Users/janhuang/Documents/htdocs',
        'REQUEST_SCHEME' => 'http',
        'CONTEXT_DOCUMENT_ROOT' => '/Users/janhuang/Documents/htdocs',
        'SCRIPT_FILENAME' => '/Users/janhuang/Documents/htdocs/me/fastd/library/http/examples/server.php',
        'GATEWAY_INTERFACE' => 'CGI/1.1',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'REQUEST_METHOD' => 'GET',
        'QUERY_STRING' => '',
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
}
