<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

use FastD\Http\JsonResponse;
use FastD\Http\ServerRequest;

include __DIR__ . '/../vendor/autoload.php';

$server = ServerRequest::createFromGlobals();

//$server->cookie->set('name', 'jan');

$content = !($server->server->isMethod('GET')) ? $server->body->all() : $server->query->all();

$response = new JsonResponse($content);

$response->withCookies($server->getCookieParams());

$response->send();
