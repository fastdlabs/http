<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/21
 * Time: 下午11:52
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

use FastD\Http\Session\Session;
use FastD\Http\Session\Storage\SessionRedis;
use FastD\Storage\Redis\Redis;

echo '<pre>';

$session = new \FastD\Http\Session\Session(new SessionRedis(new Redis([
    'host' => '11.11.11.44',
])));

//$session->set('name', 'janhuang');
//$session->set('age', 18);
print_r($session->all());

