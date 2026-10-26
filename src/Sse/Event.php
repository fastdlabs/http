<?php

declare(strict_types=1);

namespace FastD\Http\Sse;

use InvalidArgumentException;

/**
 * 单条 Server-Sent Events（SSE）事件。
 *
 * 负责把业务数据序列化为符合 SSE 规范的帧：
 *
 *     id: 1
 *     event: message
 *     retry: 3000
 *     data: hello
 *     data: world
 *     （空行）
 */
final class Event
{
    public function __construct(
        public readonly string $data = '',
        public readonly ?string $event = null,
        public readonly ?string $id = null,
        public readonly ?int $retry = null,
    ) {
        self::assertField('id', $id);
        self::assertField('event', $event);

        if ($retry !== null && $retry < 0) {
            throw new InvalidArgumentException('SSE retry must be a non-negative integer.');
        }
    }

    /**
     * 以 data 构造事件：字符串按原文输出，数组/对象自动 JSON 编码。
     *
     * @param string|array<array-key, mixed>|object $data
     */
    public static function data(
        string|array|object $data,
        ?string $event = null,
        ?string $id = null,
        ?int $retry = null,
    ): self {
        if (is_string($data)) {
            return new self($data, $event, $id, $retry);
        }

        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encoded === false) {
            throw new InvalidArgumentException('Unable to JSON encode SSE data: ' . json_last_error_msg());
        }

        return new self($encoded, $event, $id, $retry);
    }

    /**
     * 生成完整事件帧（以空行结尾）。
     */
    public function format(): string
    {
        $frame = '';

        if ($this->id !== null) {
            $frame .= 'id: ' . $this->id . "\n";
        }

        if ($this->event !== null && $this->event !== '') {
            $frame .= 'event: ' . $this->event . "\n";
        }

        if ($this->retry !== null) {
            $frame .= 'retry: ' . $this->retry . "\n";
        }

        $data = str_replace(["\r\n", "\r"], "\n", $this->data);

        foreach (explode("\n", $data) as $line) {
            $frame .= 'data: ' . $line . "\n";
        }

        return $frame . "\n";
    }

    private static function assertField(string $name, ?string $value): void
    {
        if ($value !== null && preg_match('/[\x00\r\n]/', $value) === 1) {
            throw new InvalidArgumentException(sprintf('SSE %s must not contain NUL, CR or LF characters.', $name));
        }
    }
}
