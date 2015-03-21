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
error_reporting(E_ALL);
echo '<pre>';
$composer = include __DIR__ . '/../vendor/autoload.php';

$session = new \Dobee\Http\Session\SessionHandler();

$session->setSession('name', 'janhuang');

print_r($_SESSION);

