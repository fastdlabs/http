<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/9/2
 * Time: 下午4:03
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

$storage = new \FastD\Http\Session\Storage\RedisStorage('11.11.11.33', 6379);

$handler = new \FastD\Http\Session\SessionHandler($storage);

$session = new \FastD\Http\Session\Session($handler);

//$session->setSession('name', 'janhuang', 30);
//$session->clearSession('name');
var_dump($_SESSION);
var_dump($session);


