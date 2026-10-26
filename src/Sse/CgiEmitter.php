<?php

declare(strict_types=1);

namespace FastD\Http\Sse;

/**
 * CGI / FPM 运行时的 SSE 发送器。
 *
 * 直接写入 PHP 输出缓冲并 flush；通过 connection_aborted() 与最大时长判断
 * 是否停止。生产者在长时间阻塞（如等待上游）期间无法产生心跳——需要心跳
 * 的 AI 流式场景建议使用 Swoole 运行时。
 */
final class CgiEmitter implements EmitterInterface
{
    private bool $closed = false;

    /** @var \Closure(string): void */
    private \Closure $writer;

    /** @var \Closure(): bool */
    private \Closure $aborted;

    /** @var \Closure(): float */
    private \Closure $clock;

    private ?float $deadline;

    /**
     * @param \Closure(string): void|null $writer  测试注入：替代 echo+flush
     * @param \Closure(): bool|null       $aborted 测试注入：替代 connection_aborted()
     * @param \Closure(): float|null      $clock   测试注入：替代 microtime(true)
     */
    public function __construct(
        int|float $maxDuration = 300,
        ?\Closure $writer = null,
        ?\Closure $aborted = null,
        ?\Closure $clock = null,
    ) {
        $this->writer = $writer ?? static function (string $frame): void {
            echo $frame;

            if (ob_get_level() > 0) {
                @ob_flush();
            }

            @flush();
        };
        $this->aborted = $aborted ?? static fn (): bool => connection_aborted() === 1;
        $this->clock = $clock ?? static fn (): float => microtime(true);
        $this->deadline = $maxDuration > 0 ? ($this->clock)() + (float) $maxDuration : null;
    }

    public function send(Event|string|array $event): void
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
        if ($this->isClosed() || $milliseconds < 0) {
            return;
        }

        $this->write('retry: ' . $milliseconds . "\n\n");
    }

    public function isClosed(): bool
    {
        if ($this->closed) {
            return true;
        }

        if (($this->aborted)()) {
            $this->closed = true;

            return true;
        }

        if ($this->deadline !== null && ($this->clock)() >= $this->deadline) {
            $this->closed = true;

            return true;
        }

        return false;
    }

    public function close(): void
    {
        $this->closed = true;
    }

    private function write(string $frame): void
    {
        ($this->writer)($frame);
    }

    private function normalize(Event|string|array $event): Event
    {
        return $event instanceof Event ? $event : Event::data($event);
    }
}
