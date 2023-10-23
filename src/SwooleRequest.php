<?php
declare(strict_types=1);
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use Swoole\Http\Request;


/**
 * Class SwooleServerRequest
 *
 * @package FastD\Http
 */
class SwooleRequest extends ServerRequest
{
    /**
     * @param \Swoole\Http\Request $request
     * @return SwooleRequest
     */
    public static function createServerRequestFromSwoole(Request $request): SwooleRequest
    {
        $get = $request->get ?? [];
        $post = $request->post ?? [];
        $cookie = $request->cookie ?? [];
        $files = $request->files ?? [];

        $host = '::1';
        foreach (['host', 'server_addr'] as $name) {
            if (!empty($request->header[$name])) {
                $host = parse_url($request->header[$name], PHP_URL_HOST) ?: $request->header[$name];
            }
        }

        $server = [
            'REQUEST_METHOD'    => $request->server['request_method'],
            'REQUEST_URI'       => $request->server['request_uri'],
            'PATH_INFO'         => $request->server['path_info'],
            'REQUEST_TIME'      => $request->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,
            // Server
            'SERVER_PROTOCOL'   => $request->header['server_protocol'] ?? $request->server['server_protocol'],
            'REQUEST_SCHEMA'    => $request->header['request_scheme'] ?? explode('/', $request->server['server_protocol'])[0],
            'SERVER_NAME'       => $request->header['server_name'] ?? $host,
            'SERVER_ADDR'       => $host,
            'SERVER_PORT'       => $request->header['server_port'] ?? $request->server['server_port'],
            'REMOTE_ADDR'       => $request->server['remote_addr'],
            'REMOTE_PORT'       => $request->header['remote_port'] ?? $request->server['remote_port'],
            'QUERY_STRING'      => $request->server['query_string'] ?? '',
            // Headers
            'HTTP_HOST'         => $host,
            'HTTP_USER_AGENT'   => $request->header['user-agent'] ?? '',
            'HTTP_ACCEPT'       => $request->header['accept'] ?? '*/*',
            'HTTP_ACCEPT_LANGUAGE' => $request->header['accept-language'] ?? '',
            'HTTP_ACCEPT_ENCODING' => $request->header['accept-encoding'] ?? '',
            'HTTP_CONNECTION'   => $request->header['connection'] ?? '',
            'HTTP_CACHE_CONTROL'=> $request->header['cache-control'] ?? '',
        ];

        $headers = [];
        foreach ($request->header as $name => $value) {
            $headers[$name] = $value;
        }

        $serverRequest = new static(
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
            ->withUploadedFiles($files);
    }
}
