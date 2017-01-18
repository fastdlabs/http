<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */


use FastD\Http\SwooleServerRequest;

if (!class_exists('swoole_http_request')) {
    class swoole_http_request
    {
        public $get = [];
        public $post = [];
        public $cookie = [];
        public $file = [];
        public $header = [];
        public $server = [];

        public function rawContent()
        {
            return http_build_query(['foo' => 'bar']);
        }
    }
}

if (!defined('SWOOLE_VERSION')) {
    define('SWOOLE_VERSION', '1.8.0');
}

class SwooleServerRequestTest extends PHPUnit_Framework_TestCase
{
    public function dataFromSwoole()
    {
        $swoole = new swoole_http_request();

        $swoole->get = [];
        $swoole->post = [];
        $swoole->file = [];
        $swoole->header = [
            'host' => '11.11.11.22',
            'connection' => 'keep-alive',
            'pragma' => 'no-cache',
            'cache-control' => 'no-cache',
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.75 Safari/537.36',
            'accept' => 'image/webp,image/*,*/*;q=0.8',
            'referer' => 'http://11.11.11.22:9527/',
            'accept-encoding' => 'gzip, deflate, sdch',
            'accept-language' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
        ];
        $swoole->server = [
            'request_method' => 'GET',
            'request_uri' => '/',
            'path_info' => '/',
            'request_time' => 'request_time',
            'request_time_float' => '1483065025.0912',
            'server_port' => '9527',
            'remote_port' => '49856',
            'remote_addr' => '11.11.11.1',
            'server_protocol' => 'HTTP/1.1',
            'server_software' => 'swoole-http-server'
        ];
        $swoole->cookie = [];

        return $swoole;
    }

    public function testSwooleServerRequestCreateFromSwoole()
    {
        $swoole = $this->dataFromSwoole();
        $swoole->fd = 0;
        $serverRequest = SwooleServerRequest::createServerRequestFromSwoole($swoole);
        $this->assertEmpty($serverRequest->getQueryParams());
        $this->assertEmpty($serverRequest->getParsedBody());
        $this->assertEmpty($serverRequest->getUploadedFiles());
    }
}
