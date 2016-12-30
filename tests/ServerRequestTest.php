<?php
use FastD\Http\PhpInputStream;
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
    public function dataInputFromGlobals()
    {
        fwrite(STDOUT, http_build_query($this->dataBodyFromGlobals()));
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
            ],
            'files' => [
                'name' => [
                    'MyFile.txt',
                    'MyFile2.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/plain',
                ],
                'tmp_name' => [
                    '/tmp/php/php1h4j1o',
                    '/tmp/php/php1h4j1o',
                ],
                'error' => [
                    UPLOAD_ERR_OK,
                    UPLOAD_ERR_OK
                ],
                'size' => [
                    123,
                    123
                ],
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

    public function dataPUTServerFromGlobals()
    {
        return  [
            'PHP_SELF' => '/blog/article.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_ADDR' => 'Server IP: 217.112.82.20',
            'SERVER_NAME' => 'www.blakesimpson.co.uk',
            'SERVER_SOFTWARE' => 'Apache/2.2.15 (Win32) JRun/4.0 PHP/5.2.13',
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_METHOD' => 'PUT',
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

    public function testDefaultInstanceServerRequest()
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('http', $serverRequest->getUri()->getScheme());
        $this->assertEquals('/', $serverRequest->getUri()->getPath());
    }

    public function testServerRequestQueryParams()
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $serverRequest->withQueryParams($this->dataQueryFromGlobals());
        $this->assertEquals($this->dataQueryFromGlobals(), $serverRequest->getQueryParams());
    }

    public function testServerRequestCookieParams()
    {
        $serverRequest = new ServerRequest('GET', 'http://example.com');
        $serverRequest->withCookieParams($this->dataCookiesFromGlobals());

        $this->assertEquals($this->dataCookiesFromGlobals(), $serverRequest->getCookieParams());
    }

    public function testServerRequestFromGlobals()
    {
        $_SERVER = $this->dataServerFromGlobals();
        $_COOKIE = $this->dataCookiesFromGlobals();
        $_GET = $this->dataQueryFromGlobals();
        $_POST = $this->dataBodyFromGlobals();
        $serverRequest = ServerRequest::createServerRequestFromGlobals();

        $this->assertEquals($serverRequest->getUri()->getPath(), '/blog/article.php');
        $this->assertEquals('POST', $serverRequest->getMethod());
        $this->assertEquals($this->dataCookiesFromGlobals(), $serverRequest->getCookieParams());
        $this->assertEquals($this->dataCookiesFromGlobals(), $serverRequest->getCookieParams());
        $this->assertEquals($this->dataBodyFromGlobals(), $serverRequest->getParsedBody());
    }

    public function testSerRequestFromGlobalsIsMethodPUT()
    {
        $_SERVER = $this->dataPUTServerFromGlobals();
        $_COOKIE = $this->dataCookiesFromGlobals();
        $_GET = $this->dataQueryFromGlobals();
        $_FILES = $this->dataFilesFromGlobals();
        $body = new PhpInputStream('php://temp', 'wr');
        $body->write(http_build_query($this->dataBodyFromGlobals()));

        $serverRequest = new ServerRequest('PUT', 'http://example.com/blog/articles.php', [], $body, $_SERVER);
        $serverRequest->withCookieParams($_COOKIE);
        $serverRequest->withQueryParams($_GET);
        $serverRequest->withUploadedFiles($_FILES);
        $this->assertEquals('10', $serverRequest->getParam('id'));
    }

    public function testServerRequestFilesNormalizer()
    {
        $serverRequest = new ServerRequest('PUT', 'http://example.com/blog/articles.php');
        $serverRequest->withUploadedFiles($this->dataFilesFromGlobals());
        $files = $serverRequest->getUploadedFiles();
        $this->assertNotEmpty($files);
    }
}
