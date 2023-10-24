<?php

namespace Swoole\Http;;

class Request
{
    public $get = [];
    public $post = [];
    public $file = [];
    public $header = [
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
    public $server = [
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

    public function rawContent()
    {
        return json_encode(['foo' => 'bar']);
    }
}
