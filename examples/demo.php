<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/28
 * Time: 下午3:53
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

$composer = include __DIR__ . '/../vendor/autoload.php';

$request = \Dobee\Http\Request::createGlobalRequest();

//$session = $request->getSession();
//
//print_r($session);
//
//$query = $request->getQuery();
//
//print_r($query);
//
//$cookie = $request->getCookie();
//
//$cookie->set('name', 'janhuang', time() + 120, '/');
//
//try {
//    $cookie->get('name');
//} catch (InvalidArgumentException $e) {
//    echo $e->getMessage();
//}

$response = new \Dobee\Http\JsonResponse(array(
    'path_info' => $request->getPathInfo(),
    'uri' => $request->getBaseUrl(),
    'format' => $request->getFormat(),
));

$response->send();

//$request = $request->getRequest();
//
//print_r($request->getEmail());
