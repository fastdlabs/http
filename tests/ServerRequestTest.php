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
    public function testDefaultServerRequest()
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('http', $serverRequest->getUri()->getScheme());
        $this->assertEquals('/', $serverRequest->getUri()->getPath());
    }

    public function testServerRequestParams()
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $serverRequest->withQueryParams(['name' => 'jan']);
        $this->assertEquals(['name' => 'jan'], $serverRequest->getQueryParams());

        $cookie = [
            'info' => [
                'name' => 'jan',
                'age' => 18
            ]
        ];
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $serverRequest->withCookieParams($cookie);

        $this->assertEquals($cookie, $serverRequest->getCookieParams());
    }

    public function dataFilesFromGlobals()
    {
        return [
            'file' => [
                'name' => 'MyFile.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/php/php1h4j1o',
                'error' => UPLOAD_ERR_OK,
                'size' => 123,
            ]
        ];
    }

    public function dataQueryFromGlobals()
    {
        return [
            'id' => 10,
            'user' => 'foo',
        ];
    }

    public function dataBodyFromGlobals()
    {
        return  [
            'name' => 'Pesho',
            'email' => 'pesho@example.com',
        ];
    }

    public function dataCookiesFromGlobals()
    {
        return [
            'logged-in' => 'yes!'
        ];
    }

    public function dataServerFromGlobals()
    {
        return  [
            'PHP_SELF' => '/blog/article.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_ADDR' => 'Server IP: 217.112.82.20',
            'SERVER_NAME' => 'www.blakesimpson.co.uk',
            'SERVER_SOFTWARE' => 'Apache/2.2.15 (Win32) JRun/4.0 PHP/5.2.13',
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_TIME' => 'Request start time: 1280149029',
            'QUERY_STRING' => 'id=10&user=foo',
            'DOCUMENT_ROOT' => '/path/to/your/server/root/',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-gb,en;q=0.5',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_HOST' => 'www.blakesimpson.co.uk',
            'HTTP_REFERER' => 'http://previous.url.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            'HTTPS' => '1',
            'REMOTE_ADDR' => '193.60.168.69',
            'REMOTE_HOST' => 'Client server\'s host name',
            'REMOTE_PORT' => '5390',
            'SCRIPT_FILENAME' => '/path/to/this/script.php',
            'SERVER_ADMIN' => 'webmaster@blakesimpson.co.uk',
            'SERVER_PORT' => '80',
            'SERVER_SIGNATURE' => 'Version signature: 5.123',
            'SCRIPT_NAME' => '/blog/article.php',
            'REQUEST_URI' => '/blog/article.php?id=10&user=foo',
        ];
    }

    public function testServerRequestFromGlobals()
    {
        $_SERVER = $this->dataServerFromGlobals();
        $_COOKIE = $this->dataCookiesFromGlobals();
        $_GET = $this->dataQueryFromGlobals();
        $serverRequest = ServerRequest::createServerRequestFromGlobals();

        $this->assertEquals($serverRequest->getUri()->getPath(), '/blog/article.php');
        $this->assertEquals('POST', $serverRequest->getMethod());
    }
}
