<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */
use FastD\Http\JsonResponse;
use FastD\Http\Request;
use FastD\Http\Response;
use FastD\Http\Stream;

/**
 * @param $stream
 * @param string $mode
 * @return Stream
 */
function stream($stream, $mode = 'r')
{
    return new Stream($stream, $mode);
}

/**
 * @param $method
 * @param $uri
 * @param array $headers
 * @return Request
 */
function request($method, $uri, array $headers = [])
{
    return new Request($method, $uri, $headers);
}

/**
 * @param int $statusCode
 * @param array $headers
 * @return Response
 */
function response($statusCode = 200, array $headers = [])
{
    return new Response($statusCode, $headers);
}

/**
 * @param int $statusCode
 * @param array $headers
 * @return JsonResponse
 */
function json($statusCode = 200, array $headers = [])
{
    return new JsonResponse($statusCode, $headers);
}
