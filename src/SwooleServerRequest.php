<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use Psr\Http\Message\ServerRequestInterface;
use swoole_http_request;

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

        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];

        $host = '::1';
        foreach (['host', 'server_addr'] as $name) {
            if (!empty($request->header[$name])) {
                $host = parse_url($request->header[$name], PHP_URL_HOST) ?: $request->header[$name];
            }
        }

        $server = [
            'REQUEST_METHOD' => $request->server['request_method'],
            'REQUEST_URI' => $request->server['request_uri'],
            'PATH_INFO' => $request->server['path_info'],
            'REQUEST_TIME' => $request->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,
            // Server
            'SERVER_PROTOCOL' => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'REQUEST_SCHEMA' => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/', $request->server['server_protocol'])[0],
            'SERVER_NAME' => isset($request->header['server_name']) ? $request->header['server_name'] : $host,
            'SERVER_ADDR' => $host,
            'SERVER_PORT' => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REMOTE_ADDR' => self::getServerRequestIp($request),
            'REMOTE_PORT' => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
            'QUERY_STRING' => isset($request->server['query_string']) ? $request->server['query_string'] : '',
            // Headers
            'HTTP_HOST' => $host,
            'HTTP_USER_AGENT' => isset($request->header['user-agent']) ? $request->header['user-agent'] : '',
            'HTTP_ACCEPT' => isset($request->header['accept']) ? $request->header['accept'] : '*/*',
            'HTTP_ACCEPT_LANGUAGE' => isset($request->header['accept-language']) ? $request->header['accept-language'] : '',
            'HTTP_ACCEPT_ENCODING' => isset($request->header['accept-encoding']) ? $request->header['accept-encoding'] : '',
            'HTTP_CONNECTION' => isset($request->header['connection']) ? $request->header['connection'] : '',
            'HTTP_CACHE_CONTROL' => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
        ];

        $headers = [];
        foreach ($request->header as $name => $value) {
            $headers[str_replace('-', '_', $name)] = $value;
        }

        $serverRequest = new ServerRequest(
            $server['REQUEST_METHOD'],
            static::createUriFromGlobal($server),
            $headers,
            null,
            $server
        );
        unset($headers);

        $serverRequest->getBody()->write($request->rawContent());

        return $serverRequest
            ->withParsedBody($post)
            ->withQueryParams($get)
            ->withCookieParams($cookie)
            ->withUploadedFiles($files)
            ;
    }

    /**
     *
     * @param swoole_http_request $request
     *
     * @return string
     */
    public static function getServerRequestIp(swoole_http_request $request)
    {
        $ip = '';

        // get ip from server env
        $_server = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($_server as $item) {
            $item = strtolower($item);

            if (isset($request->server[$item])) {
                if (is_array($request->server[$item])) {
                    list($ip) = explode(',', current($request->server[$item]));
                    break;
                } else {
                    list($ip) = explode(',', $request->server[$item]);
                    break;
                }
            }
        }

        if (empty($ip)) {
            // get ip from proxy set header or header
            if (isset($request->header['x-forwarded-for'])) {
                is_array($request->header['x-forwarded-for']) ?
                    list($ip) = explode(',', current($request->header['x-forwarded-for'])) :
                    list($ip) = explode(',', $request->header['x-forwarded-for']);
            } elseif (isset($request->header['x-real-ip'])) {
                $ip = is_array($request->header['x-real-ip']) ?
                    current($request->header['x-real-ip']) : $request->header['x-real-ip'];
            }
        }

        return $ip ?: '::1';
    }
}
