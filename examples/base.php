<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午4:01
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */
include __DIR__ . '/../vendor/autoload.php';

$request = \FastD\Protocol\Http\Request::createRequestHandle();

echo '<pre>';
print_r($request->server);
var_dump($request->isXmlHttpRequest());
var_dump($request->getMethod());
var_dump('path info: ' . $request->getPathInfo());
var_dump($request->getFormat());
var_dump($request->getClientIp());
var_dump('base url: ' . $request->getBaseUrl());
var_dump($request->getRequestUri());
var_dump('root path: ' . $request->getRootPath());
var_dump($request->query->hasGet('name', 'janhuang'));