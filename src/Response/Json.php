<?php
declare(strict_types=1);

namespace FastD\Http\Response;

use ArrayAccess;

class Json extends Text implements ArrayAccess
{
    public function __construct(protected array $parsedBody = [], int $status = StatusCodeInterface::HTTP_OK, array $headers = [])
    {
        $json = json_encode($this->parsedBody, JSON_UNESCAPED_UNICODE);

        $this->withHeader('Content-Type', 'application/json; charset=UTF-8');

        parent::__construct($json, $status, $headers);
    }

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
