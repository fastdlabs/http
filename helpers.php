<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

/**
 * @param $method
 * @param $uri
 * @param array $headers
 * @return \FastD\Http\Request
 */
function request ($method, $uri, array $headers  = []) {
    return new \FastD\Http\Request($method, $uri, $headers);
}

/**
 * @param int $statusCode
 * @param array $headers
 * @return \FastD\Http\Response
 */
function response ($statusCode = 200, array $headers = []) {
    return new \FastD\Http\Response($statusCode, $headers);
}

/**
 * @param int $statusCode
 * @param array $headers
 * @return \FastD\Http\JsonResponse
 */
function json ($statusCode = 200, array $headers = []) {
    return new \FastD\Http\JsonResponse($statusCode, $headers);
}