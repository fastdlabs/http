<?php

namespace request;

use FastD\Http\Request\SwooleRequest;

if (!defined('SWOOLE_VERSION')) {
    define('SWOOLE_VERSION', '5.1.0');
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
