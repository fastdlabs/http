<?php
declare(strict_types=1);

namespace FastD\Http\Request;

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

    public function raw()
    {
        return $this->payload;
    }

    public function json()
    {
        $jsonOptions = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        
        if (is_array($this->payload)) {
            return json_encode($this->payload, $jsonOptions);
        }
        
        // 如果payload已经是字符串但看起来像数组/对象，尝试解析后再编码
        $parsed = json_decode($this->payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return json_encode($parsed, $jsonOptions);
        }
        
        // 如果是原始字符串，尝试解析为数组再编码
        $data = $this->payload;
        if (!is_array($data)) {
            $data = ['data' => $data];
        }
        
        return json_encode($data, $jsonOptions);
    }

    public function text()
    {
        if (is_array($this->payload)) {
            return implode("\n", array_map(function($key, $value) {
                return $key . ': ' . $value;
            }, array_keys($this->payload), array_values($this->payload)));
        }
        
        return (string) $this->payload;
    }

    public function formData()
    {
        if (is_array($this->payload)) {
            return http_build_query($this->payload);
        }
        
        // 如果已经是字符串形式的表单数据，直接返回
        return (string) $this->payload;
    }

    public function __toString(): string
    {
        return $this->payload;
    }
}