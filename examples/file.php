<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/23
 * Time: 下午12:07
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */
include __DIR__ . '/../vendor/autoload.php';

use FastD\Http\Request;

$request = Request::createRequestHandle();

if ($request->isMethod('post')) {
    echo '<pre>';
    print_r($request->getUploader()->uploadTo(__DIR__ . '/uploaded'));
    echo '</pre>';
    echo '<br />';
}

?>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit">
</form>
</body>
</html>

