<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */


use FastD\Http\SwooleRequest;

if (!defined('SWOOLE_VERSION')) {
    define('SWOOLE_VERSION', '1.8.0');
}

class SwooleRequestTest extends \PHPUnit\Framework\TestCase
{
    public function dataFromSwoole()
    {
        return new \Swoole\Http\Request();
    }

    public function testSwooleServerRequestCreateFromSwoole()
    {
        $swoole = $this->dataFromSwoole();
        $swoole->fd = 0;
        $serverRequest = SwooleRequest::createServerRequestFromSwoole($swoole);
        $this->assertEmpty($serverRequest->getQueryParams());
        $this->assertEmpty($serverRequest->getParsedBody());
        $this->assertEmpty($serverRequest->getUploadedFiles());
        $this->assertEquals('/', $serverRequest->getServerParams()['PATH_INFO']);
    }
}
