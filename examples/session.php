<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/5/19
 * Time: 上午11:26
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */
$composer = include __DIR__ . '/../vendor/autoload.php';

$request = \Dobee\Http\Request::createGlobalRequest();

/**
 * 因为session较为特殊，所以这里需要用`getSession`方法获取
 */
$session = $request->getSession();
//$session = new \Dobee\Http\Session\SessionBag();;
//$session = $request->getSession(new MysqlSession());

echo '<pre>';

$session->setSession('name', 'janhaung');
print_r($session);
print_r($_SESSION);
print_r($session->getSession('name'));