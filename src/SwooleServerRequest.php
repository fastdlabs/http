<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use FastD\Session\SessionHandler;
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
     * @var swoole_http_response
     */
    protected $response;

    /**
     * SwooleServerRequest constructor.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     * @param array $cookie
     * @param array $server
     * @param $sessionHandler
     */
    public function __construct(array $get, array $post, array $files, array $cookie, array $server, SessionHandler $sessionHandler = null)
    {
        parent::__construct($get, $post, $files, $cookie, $server, $sessionHandler);
    }

    /**
     * @param swoole_http_response $response
     */
    public function setSwooleResponse(swoole_http_response $response)
    {
        $this->response = $response;
    }

    /**
     * 处理 swoole 请求
     *
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @param SessionHandler $sessionHandler
     * @return SwooleServerRequest
     */
    public static function createFromSwoole(swoole_http_request $request, swoole_http_response $response, SessionHandler $sessionHandler = null)
    {
        $config = [
            'document_root' => realpath('.'),
            'script_name' => __FILE__,
        ];

        $config['script_filename'] = str_replace('//', '/', $config['document_root'] . '/' . $config['script_name']); // Equal nginx fastcgi_params $document_root$fastcgi_script_name;

        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];
        $server = (function (swoole_http_request $request, $config) {
            return [
                // Server
                'REQUEST_METHOD' => $request->server['request_method'],
                'REQUEST_URI' => $request->server['request_uri'],
                'PATH_INFO' => $request->server['path_info'],
                'REQUEST_TIME' => $request->server['request_time'],
                'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,

                // Swoole and general server proxy or server configuration.
                'SERVER_PROTOCOL' => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
                'REQUEST_SCHEMA' => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/', $request->server['server_protocol'])[0],
                'SERVER_NAME' => isset($request->header['server_name']) ? $request->header['server_name'] : $request->header['host'],
                'SERVER_ADDR' => isset($request->header['server_addr']) ? $request->header['server_addr'] : $request->header['host'],
                'SERVER_PORT' => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
                'REMOTE_ADDR' => isset($request->header['remote_addr']) ? $request->header['remote_addr'] : $request->server['remote_addr'],
                'REMOTE_PORT' => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
                'QUERY_STRING' => isset($request->server['query_string']) ? $request->server['query_string'] : '',
                'DOCUMENT_ROOT' => $config['document_root'],
                'SCRIPT_FILENAME' => $config['script_filename'],
                'SCRIPT_NAME' => '/' . $config['script_name'],
                'PHP_SELF' => '/' . $config['script_name'],
                // Headers
                'HTTP_HOST' => $request->header['host'] ?? '::1',
                'HTTP_USER_AGENT' => $request->header['user-agent'] ?? '',
                'HTTP_ACCEPT' => $request->header['accept'] ?? '*/*',
                'HTTP_ACCEPT_LANGUAGE' => $request->header['accept-language'] ?? '',
                'HTTP_ACCEPT_ENCODING' => $request->header['accept-encoding'] ?? '',
                'HTTP_CONNECTION' => $request->header['connection'] ?? '',
                'HTTP_CACHE_CONTROL' => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
            ];
        })($request, $config);

        return new static($get, $post, $files, $cookie, $server, $sessionHandler);
    }
}