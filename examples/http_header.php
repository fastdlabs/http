<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

use FastD\Http\ServerRequest;
use FastD\Session\Session;

include __DIR__ . '/../vendor/autoload.php';

$server = ServerRequest::createFromGlobals();

echo '<pre>';
print_r($server->header);
print_r($server->server);
echo $server->header->hasGet(Session::SESSION_KEY, null);

