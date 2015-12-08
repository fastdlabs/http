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
    public static function createRequestHandle(\swoole_http_request $request, array $config = null)
    {
        $get        = isset($request->get) ? $request->get : [];
        $post       = isset($request->post) ? $request->post : [];
        $cookies    = isset($request->cookie) ? $request->cookie : [];
        $files      = isset($request->files) ? $request->files : [];
        $server     = self::initSwooleHttpRequestServer($request, $config);

        return new Request($get, $post, $files, $cookies, $server);
    }

    /**
     * @param \swoole_http_request $request
     * @param array $config
     * @return array
     */
    public static function initSwooleHttpRequestServer(\swoole_http_request $request, array $config = null)
    {
        return [
            // Server
            'REQUEST_METHOD'    => $request->server['request_method'],
            'REQUEST_URI'       => $request->server['request_uri'],
            'PATH_INFO'         => $request->server['path_info'],
            'SERVER_PROTOCOL'   => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
            'SERVER_NAME'       => isset($request->header['server_name']) ? $request->header['server_name'] : $request->header['host'],
            'SERVER_ADDR'       => isset($request->header['server_addr']) ? $request->header['server_addr'] : $request->header['host'],
            'REMOTE_ADDR'       => isset($request->header['remote_addr']) ? $request->header['remote_addr'] : $request->server['remote_addr'],
            'SERVER_PORT'       => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
            'REQUEST_TIME'      => $request->server['request_time'],
            'SCRIPT_FILENAME'   => isset($request->header['script_filename']) ? $request->header['script_filename'] : __FILE__,
            'DOCUMENT_ROOT'     => isset($request->header['document_root']) ? $request->header['document_root']: '',
            'GATEWAY_INTERFACE' => 'fastd_swoole/' . SWOOLE_VERSION,
            'QUERY_STRING'      => isset($request->header['query_string']) ? $request->header['query_string'] : '',
            'SCRIPT_NAME'       => '',
            'PHP_SELF'          => '',

            // Header
            'HTTP_HOST'         => $request->header['host'],
            'HTTP_USER_AGENT'   => $request->header['user-agent'],
            'HTTP_ACCEPT'       => $request->header['accept'],
            'HTTP_ACCEPT_LANGUAGE' => $request->header['accept-language'],
            'HTTP_ACCEPT_ENCODING' => $request->header['accept-encoding'],
            'HTTP_CONNECTION'   => $request->header['connection'],
            'HTTP_CACHE_CONTROL'=> $request->header['cache-control'],
        ];
    }
}