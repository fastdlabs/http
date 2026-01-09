<?php

declare(strict_types=1);

namespace FastD\Http\Response;

use FastD\Http\Uri;

class Redirect extends Text
{
    public function __construct(string $uri, int $status = StatusCode::HTTP_FOUND, array $headers = [], string $protocolVersion = '1.1')
    {
        $uri = new Uri($uri);

        parent::__construct($status, '', $headers, $protocolVersion);

        // 在构造函数中直接设置头部，因为withHeader是不可变的
        $this->headers['location'] = [(string) $uri];
    }
}
