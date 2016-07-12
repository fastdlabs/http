<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

include __DIR__ . '/../vendor/autoload.php';

use FastD\Swoole\Http;

class SessionDemo extends Http\HttpServer
{
    /**
     * @param \FastD\Http\Request $request
     * @return \FastD\Http\Response
     */
    public function doRequest(\FastD\Http\Swoole\SwooleRequest $request)
    {
        return $this->html('hello');
    }
}

SessionDemo::run([]);

