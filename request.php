<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

include __DIR__ . '/vendor/autoload.php';

$request = new \FastD\Http\Request('POST', new \FastD\Http\Uri('http://api.linghit.com/'));

$response = $request->send();
echo $response;
$content = $response->getBody();
$content = json_encode(json_decode($content, true), JSON_PRETTY_PRINT);
echo $content;
