<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */


use FastD\Http\SwooleServerRequest;

class swoole_http_request2 {
    public $get = [];
    public $post = [];
    public $cookie = [];
    public $file = [];
    public $header = [];
    public $server = [];
}

class SwooleServerRequestTest extends PHPUnit_Framework_TestCase
{
    public function dataFromSwoole()
    {
        $swoole = new swoole_http_request();

        $swoole->get = [];
        $swoole->post = [];
        $swoole->file = [];
        $swoole->header = [];
        $swoole->server = [
            'request_method' => 'POST',
            'request_uri' => '/blog/article.php',
            'path_info' => '/blog/article.php',
            'request_time' => time(),
            'server_protocol' => 'http',
        ];
        $swoole->cookie = [];

        return $swoole;
    }

    public function testSwooleServerRequestCreateFromSwoole()
    {
        $swoole = $this->dataFromSwoole();
        $serverRequest = SwooleServerRequest::createServerRequestFromSwoole($swoole);
        print_r($serverRequest);
    }
}
