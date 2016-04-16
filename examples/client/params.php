<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/4/16
 * Time: 上午10:40
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

if ('GET' == $_SERVER['REQUEST_METHOD']) {
    $content = $_GET;
} else if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $content = $_POST;
} else {
    parse_str(file_get_contents('php://input'), $content);
}

print_r($content);