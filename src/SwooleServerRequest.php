<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use Psr\Http\Message\ServerRequestInterface;
use swoole_http_request;
use swoole_http_response;

/**
 * Class SwooleServerRequest
 *
 * @package FastD\Http
 */
class SwooleServerRequest extends ServerRequest
{
    /**
     * @param swoole_http_request $request
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromSwoole(swoole_http_request $request)
    {
        $_GET = isset($request->get) ? $request->get : [];
        $_POST = isset($request->post) ? $request->post : [];
        $_COOKIE = isset($request->cookie) ? $request->cookie : [];
        $_FILES = isset($request->files) ? $request->files : [];
        $_SERVER = [
            'REQUEST_METHOD' => $request->server['request_method'],
            'REQUEST_URI' => $request->server['request_uri'],
            'PATH_INFO' => $request->server['path_info'],
            'REQUEST_TIME' => $request->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,

            'SERVER_PROTOCOL' => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'REQUEST_SCHEMA' => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/', $request->server['server_protocol'])[0],
            'SERVER_NAME' => isset($request->header['server_name']) ? $request->header['server_name'] : $request->header['host'],
            'SERVER_ADDR' => isset($request->header['server_addr']) ? $request->header['server_addr'] : $request->header['host'],
            'SERVER_PORT' => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REMOTE_ADDR' => isset($request->header['remote_addr']) ? $request->header['remote_addr'] : $request->server['remote_addr'],
            'REMOTE_PORT' => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
            'QUERY_STRING' => isset($request->server['query_string']) ? $request->server['query_string'] : '',
            // Headers
            'HTTP_HOST' => $request->header['host'] ?? '::1',
            'HTTP_USER_AGENT' => $request->header['user-agent'] ?? '',
            'HTTP_ACCEPT' => $request->header['accept'] ?? '*/*',
            'HTTP_ACCEPT_LANGUAGE' => $request->header['accept-language'] ?? '',
            'HTTP_ACCEPT_ENCODING' => $request->header['accept-encoding'] ?? '',
            'HTTP_CONNECTION' => $request->header['connection'] ?? '',
            'HTTP_CACHE_CONTROL' => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
        ];;

        return parent::createServerRequestFromGlobals();
    }
}