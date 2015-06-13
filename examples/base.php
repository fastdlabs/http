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

$request = \Dobee\Protocol\Http\Request::createRequestHandle();

echo '<pre>';
print_r($_SERVER);
var_dump($request->isXmlHttpRequest());
var_dump($request->getMethod());
var_dump($request->getPathInfo());
var_dump($request->getFormat());
