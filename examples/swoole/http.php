<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

use FastD\Http\SwooleServerRequest;

include __DIR__ . '/../../vendor/autoload.php';

$http = new swoole_http_server("127.0.0.1", 9501);

$http->on('request', function ($request, $response) {
    $server = SwooleServerRequest::createFromSwoole($request, $response);
    $server->response($server->server->getPathInfo());
});

$http->start();

