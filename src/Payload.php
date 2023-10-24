<?php

namespace FastD\Http;

class Payload
{
    protected string $payload = '';

    public function __construct($data = '')
    {
        if (is_array($data)) {
            $data = http_build_query($data);
        }
        $this->payload = $data;
    }

    public function __toString(): string
    {
        return $this->payload;
    }
}
