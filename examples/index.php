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
echo '<pre>';
print_r($_SESSION);
print_r($_COOKIE);
$request->cookies->setCookie('name', 'janhuang');
$request->session->setSession('name', 'janhuang');
writeln($request->query->get('name'));
writeln($request->request->get('name'));
writeln($request->cookies->getCookie('name')->getValue());
writeln($request->session->getSession('name')->getValue());
print_r($request->session);
echo '<hr />';
print_r($request);

function writeln($message, $EOL = "<br />")
{
    echo $message . $EOL;
}


