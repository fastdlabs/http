<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/12/23
 * Time: 下午4:02
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

$request = \FastD\Http\Request::createRequestHandle();

$response = $request->createRequest('http://localhost/me/fastd/library/http/examples/base.php')->ajax();
echo '<pre>';
print_r($response);