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

$request = \Dobee\Http\Request::createGlobalRequest();

echo \Dobee\Http\Bag\Filter::getEmail('384099566@qq.com') . '<br />';

echo \Dobee\Http\Bag\Filter::getEnglish('abcNi 哈') . '<br />';

echo \Dobee\Http\Bag\Filter::getBr("hello \n world") . '<br />';

echo \Dobee\Http\Bag\Filter::getNl('hello<br />world') . '<br />';

echo \Dobee\Http\Bag\Filter::getFloat(12) . '<br />';

echo \Dobee\Http\Bag\Filter::getInt(12) . '<br />';

echo \Dobee\Http\Bag\Filter::getString('hello world<script>alert(test);</script>') . '<br />';

echo \Dobee\Http\Bag\Filter::getPlainText('<ul><li>hel woorladf</li></ul>');
