<?php

declare(strict_types=1);

namespace FastD\Http\Sse;

/**
 * SSE 发送器公共实现。
 *
 * 统一帧归一化、注释帧、重连间隔和关闭状态；具体运行时只需实现 write()。
 */
abstract class AbstractEmitter implements EmitterInterface
{
    protected bool $closed = false;

    public function send(string|array|object $event): void
    {
        if ($this->isClosed()) {
            return;
        }

        $this->write($this->normalize($event)->format());
    }

    public function comment(string $text): void
    {
        if ($this->isClosed()) {
            return;
        }

        $this->write(': ' . str_replace(["\r", "\n"], ' ', $text) . "\n\n");
    }

    public function retry(int $milliseconds): void
    {
        if ($milliseconds < 0 || $this->isClosed()) {
            return;
        }

        $this->write('retry: ' . $milliseconds . "\n\n");
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(): void
    {
        $this->closed = true;
    }

    abstract protected function write(string $frame): void;

    protected function normalize(string|array|object $event): Event
    {
        return $event instanceof Event ? $event : Event::data($event);
    }
}
