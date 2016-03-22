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



echo '<pre>';

$redis = new \FastD\Http\Session\Storage\RedisStorage();

$session = new \FastD\Http\Session\Session($redis);

//$session->set('name', 'janhuang');
//$session->set('age', 18);
print_r($session->all());