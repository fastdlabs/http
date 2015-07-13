<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 上午11:48
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

$composer = include __DIR__ . '/../vendor/autoload.php';

$request = \FastD\Http\Request::createRequestHandle();

$session = $request->getSessionHandle();
//$session->set('name', 'janhuang');
echo '<pre>';
print_r($session);