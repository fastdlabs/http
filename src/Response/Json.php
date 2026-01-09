<?php

declare(strict_types=1);

namespace FastD\Http\Response;

use ArrayAccess;
use FastD\Http\Stream\Stream;
use Psr\Http\Message\StreamInterface;

class Json extends Text implements ArrayAccess
{
    public function __construct(int $status = StatusCode::HTTP_OK, protected array $parsedBody = [], array $headers = [], string $protocolVersion = '1.1')
    {
        $json = json_encode($this->parsedBody, JSON_UNESCAPED_UNICODE);
        
        // 检查 JSON 编码是否成功
        if ($json === false) {
            $json = '{}'; // 提供默认值
        }

        // 添加 Content-Type 到头部数组中
        if (!isset($headers['content-type'])) {
            $headers['content-type'] = 'application/json; charset=UTF-8';
        }

        parent::__construct($status, $json, $headers, $protocolVersion);
    }

    // 下方 array access 实现只能改变 parsed body，无法对 stream 进行改写
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->parsedBody[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->parsedBody[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->parsedBody[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->parsedBody[$offset]);
    }
}