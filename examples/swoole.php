<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/12/8
 * Time: 下午12:00
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

$server = new \swoole_http_server('0.0.0.0', 9600);

$server->on('request', function ($request, $response) {
    $document_root = __DIR__;
    $script_name = 'swoole.php';
    print_r($request);
    $request = \FastD\Http\SwooleRequest::createRequestHandle($request, [
        'document_root'     => $document_root,
        'script_name'       => $script_name
    ]);
    print_r($request);

    $response->write('base : ' . $request->getBaseUrl() . '<br />');
    $response->write('request_uri: ' . $request->getRequestUri() . '<br />');
    $response->write('path_info: ' . $request->getPathInfo());
    $response->end('');
});

$server->start();