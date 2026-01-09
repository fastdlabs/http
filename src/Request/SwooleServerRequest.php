<?php

declare(strict_types=1);

namespace FastD\Http\Request;

use FastD\Http\Stream\PhpInputStream;
use FastD\Http\Stream\Stream;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class SwooleServerRequest extends ServerRequest
{
    public static function fromSwoole(Request $request): SwooleServerRequest
    {
        $server = static::createServerFormSwoole($request);
        $version = str_replace('HTTP/', '', $server['SERVER_PROTOCOL']);
        // 为满足非 POST 等请求的请求体在初始化时候的解析
        $serverRequest = new static($server['REQUEST_METHOD'], static::createUriFromBoth($server), $request->header, Stream::create($request->getContent()), $version, $server);

        return $serverRequest->withQueryParams($request->get)
            ->withParsedBody($request->post)
            ->withCookieParams($request->cookie ?? [])
            ->withUploadedFiles(static::normalizeFiles($request->files));
    }

    /**
     * 返回模拟 cgi 环境下 $_SERVER 的系统请求信息，因此是全大写
     *
     * @param Request $swooleRequest
     * @return array
     */
    protected static function createServerFormSwoole(Request $swooleRequest): array
    {
        $host = '::1';
        foreach (['host', 'server_addr'] as $name) {
            if (!empty($swooleRequest->header[$name])) {
                $host = parse_url($swooleRequest->header[$name], PHP_URL_HOST) ?: $swooleRequest->header[$name];
            }
        }

        return [
            'REQUEST_METHOD'    => $swooleRequest->server['request_method'],
            'REQUEST_URI'       => $swooleRequest->server['request_uri'],
            'PATH_INFO'         => $swooleRequest->server['path_info'],
            'REQUEST_TIME'      => $swooleRequest->server['request_time'],
            'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,
            // Server
            'SERVER_PROTOCOL'   => $swooleRequest->header['server_protocol'] ?? $swooleRequest->server['server_protocol'],
            'REQUEST_SCHEMA'    => $swooleRequest->header['request_scheme'] ?? explode('/', $swooleRequest->server['server_protocol'])[0],
            'SERVER_NAME'       => $swooleRequest->header['server_name'] ?? $host,
            'SERVER_ADDR'       => $host,
            'SERVER_PORT'       => $swooleRequest->header['server_port'] ?? $swooleRequest->server['server_port'],
            'REMOTE_ADDR'       => $swooleRequest->server['remote_addr'],
            'REMOTE_PORT'       => $swooleRequest->header['remote_port'] ?? $swooleRequest->server['remote_port'],
            'QUERY_STRING'      => $swooleRequest->server['query_string'] ?? '',
            // Headers
            'HTTP_HOST'         => $host,
            'HTTP_USER_AGENT'   => $swooleRequest->header['user-agent'] ?? '',
            'HTTP_ACCEPT'       => $swooleRequest->header['accept'] ?? '*/*',
            'HTTP_ACCEPT_LANGUAGE' => $swooleRequest->header['accept-language'] ?? '',
            'HTTP_ACCEPT_ENCODING' => $swooleRequest->header['accept-encoding'] ?? '',
            'HTTP_CONNECTION'   => $swooleRequest->header['connection'] ?? '',
            'HTTP_CACHE_CONTROL'=> $swooleRequest->header['cache-control'] ?? '',
        ];
    }
}
