<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/21
 * Time: 下午11:14
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

include __DIR__ . '/../vendor/autoload.php';

use FastD\Http\Attribute\CookiesAttribute;

$cookie = new CookiesAttribute($_COOKIE);

//$cookie->set('name', 'janhuang');
//setcookie('age', 18);

echo '<pre>';
print_r($cookie->all());

try {
    echo $cookie->get('name')->asString();
} catch (Exception $e) {
    echo 'null';
}

