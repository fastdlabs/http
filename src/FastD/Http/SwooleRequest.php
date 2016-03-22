<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/12/8
 * Time: 上午11:24
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http;

/**
 * Swoole extension http server request handle.
 *
 * Class SwooleRequest
 *
 * @package FastD\Http
 */
class SwooleRequest extends Request
{
    /**
     * @param \swoole_http_request $request
     * @param array $config
     * @return Request|static
     */
    public static function createSwooleRequestHandle(\swoole_http_request $request, array $config = [])
    {
        $config = array_merge([
            'document_root'     => '',
            'script_name'       => '',
        ], $config);

        $config['script_filename'] = str_replace('//', '/', $config['document_root'] . '/' . $config['script_name']); // Equal nginx fastcgi_params $document_root$fastcgi_script_name;

        unset($presetConfig);

        $get        = isset($request->get) ? $request->get : [];
        $post       = isset($request->post) ? $request->post : [];
        $cookies    = isset($request->cookie) ? $request->cookie : [];
        $files      = isset($request->files) ? $request->files : [];
        $server     = self::initSwooleHttpRequestServer($request, $config);

        unset($config);

        return new Request($get, $post, $files, $cookies, $server);
    }

    /**
     * @param \swoole_http_request $request
     * @param array $config
     * @return array
     */
    public static function initSwooleHttpRequestServer(\swoole_http_request $request, array $config = [])
    {
        return [
            // Server
            'REQUEST_METHOD'    => $request->server['request_method'],
            'REQUEST_URI'       => $request->server['request_uri'],
            'PATH_INFO'         => $request->server['path_info'],
            'REQUEST_TIME'      => $request->server['request_time'],
            'GATEWAY_INTERFACE' => 'fastd_swoole/' . SWOOLE_VERSION,
            // Swoole and general server proxy or server configuration.
            'SERVER_PROTOCOL'   => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'REQUEST_SCHEMA'    => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/',$request->server['server_protocol'])[0],
            'SERVER_NAME'       => isset($request->header['server_name']) ? $request->header['server_name'] : $request->header['host'],
            'SERVER_ADDR'       => isset($request->header['server_addr']) ? $request->header['server_addr'] : $request->header['host'],
            'SERVER_PORT'       => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REMOTE_ADDR'       => isset($request->header['remote_addr']) ? $request->header['remote_addr'] : $request->server['remote_addr'],
            'REMOTE_PORT'       => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
            'QUERY_STRING'      => isset($request->server['query_string']) ? $request->server['query_string'] : '',
            'DOCUMENT_ROOT'     => $config['document_root'],
            'SCRIPT_FILENAME'   => $config['script_filename'],
            'SCRIPT_NAME'       => '/' . $config['script_name'],
            'PHP_SELF'          => '/' . $config['script_name'],
            'HTTP_FD'           => $request->fd,

            // Header
            'HTTP_HOST'             => $request->header['host'],
            'HTTP_USER_AGENT'       => $request->header['user-agent'],
            'HTTP_ACCEPT'           => $request->header['accept'],
            'HTTP_ACCEPT_LANGUAGE'  => $request->header['accept-language'],
            'HTTP_ACCEPT_ENCODING'  => $request->header['accept-encoding'],
            'HTTP_CONNECTION'       => $request->header['connection'],
            'HTTP_CACHE_CONTROL'    => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
        ];
    }
}