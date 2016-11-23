<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Factories;

use swoole_http_request;
use swoole_http_response;
use FastD\Session\SessionHandler;

/**
 * Interface SwooleServerRequestFactoryInterface
 *
 * @package FastD\Http\Factories
 */
interface SwooleServerRequestFactoryInterface extends ServerRequestFactoryInterface
{
    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @param SessionHandler|null $sessionHandler
     * @return mixed
     */
    public function createServerRequestFromSwoole(swoole_http_request $request, swoole_http_response $response, SessionHandler $sessionHandler = null);
}