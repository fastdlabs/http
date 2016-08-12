<?php

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
    protected $_server = [];

    public function setUp()
    {
        $this->_server = [
            'UNIQUE_ID' => 'Vu7L0sCoAGgAAANtP1sAAAAB',
            'HTTP_HOST' => 'localhost',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36',
            'HTTP_REFERER' => 'http://localhost/me/fastd/library/http/examples/',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
            'PATH' => '/usr/bin:/bin:/usr/sbin:/sbin',
            'DYLD_LIBRARY_PATH' => '/Applications/XAMPP/xamppfiles/lib:/Applications/XAMPP/xamppfiles/lib',
            'SERVER_SIGNATURE' => '',
            'SERVER_SOFTWARE' => 'Apache/2.4.18 (Unix) OpenSSL/1.0.2e PHP/7.0.0 mod_perl/2.0.8-dev Perl/v5.16.3',
            'SERVER_NAME' => 'localhost',
            'SERVER_ADDR' => '::1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '::1',
            'DOCUMENT_ROOT' => '/Users/janhuang/Documents/htdocs',
            'REQUEST_SCHEME' => 'http',
            'CONTEXT_PREFIX' => '',
            'CONTEXT_DOCUMENT_ROOT' => '/Users/janhuang/Documents/htdocs',
            'SERVER_ADMIN' => 'you@example.com',
            'SCRIPT_FILENAME' => '/Users/janhuang/Documents/htdocs/me/fastd/library/http/examples/server.php',
            'REMOTE_PORT' => '49949',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/me/fastd/library/http/examples/server.php',
            'SCRIPT_NAME' => '/me/fastd/library/http/examples/server.php',
            'PHP_SELF' => '/me/fastd/library/http/examples/server.php',
        ];
    }

    public function testPathInfo()
    {
        echo 1;
    }
}
