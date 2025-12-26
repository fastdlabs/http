<?php
declare(strict_types=1);

namespace FastD\Http\Request;

use RuntimeException;

class Payload
{
    public function __construct(protected ?array $payload = [])
    {
    }

    public function json(): string
    {
        $result = json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON encode error: ' . json_last_error_msg());
        }
        return $result;
    }

    public function text(): string
    {
        return implode("\n", array_map(function($key, $value) {
            return $key . ': ' . $value;
        }, array_keys($this->payload), array_values($this->payload)));
    }

    public function formData(): string
    {
        return http_build_query($this->payload);
    }

    public function __toString(): string
    {
//        return $this->payload;
    }
}