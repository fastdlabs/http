<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/2
 * Time: 下午2:15
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

error_reporting(E_ALL);

$composer = include __DIR__ . '/../vendor/autoload.php';

$request = \FastD\Http\Request::createRequestHandle();

if ($request->isMethod('post')) {
    echo '<pre>';

    $uploaded = $request->files->getUploader([
        'max.size' => '10M',
        'save.path' => __DIR__ . '/uploaded',
    ]);

    $uploadFiles = $uploaded->uploading()->getUploadFiles();

    print_r($uploadFiles);
}
?>

<!DOCTYPE>
<html>
<head>
    <meta charset="utf-8"/>
</head>
<body>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file[]" id=""/>
    <input type="submit" value=""/>
</form>
</body>
</html>
