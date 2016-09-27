<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

use FastD\Http\Response;
use FastD\Http\ServerRequest;

include __DIR__ . '/../vendor/autoload.php';

$server = ServerRequest::createFromGlobals();

$server->cookie->set('name', 'jan');

$response = new Response('hello world');

$response->withCookies($server->getCookieParams());

$response->send();
