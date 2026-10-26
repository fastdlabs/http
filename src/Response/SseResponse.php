<?php

declare(strict_types=1);

namespace FastD\Http\Response;

use FastD\Http\Sse\CgiEmitter;
use FastD\Http\Sse\EmitterInterface;
use FastD\Http\Sse\Event;
use InvalidArgumentException;
use Throwable;

/**
 * Server-Sent Events 流式响应（CGI / FPM）。
 *
 * 用法一：生成器
 *
 *     return sse((function () {
 *         yield ['tick' => 1];
 *         yield Event::data('done', 'done');
 *     })());
 *
 * 用法二：Emitter 回调（可主动控制心跳/结束）
 *
 *     return sse(function (EmitterInterface $emitter): void {
 *         $emitter->comment('connected');
 *         $emitter->send(Event::data('hello'));
 *     });
 *
 * Swoole 运行时由 RequestListener 检测本类型并改用 SwooleEmitter 输出。
 */
final class SseResponse extends Text implements StreamingResponseInterface
{
    public const DEFAULT_HEARTBEAT = 15;
    public const DEFAULT_MAX_DURATION = 300;

    private \Closure $producer;

    private int|float $heartbeat;

    private int|float $maxDuration;

    private ?int $retry;

    private ?string $errorEvent;

    private ?\Closure $onError;

    /**
     * @param iterable<Event|string|array>|\Closure(EmitterInterface): void $producer
     * @param array{
     *     heartbeat?: int,
     *     max_duration?: int,
     *     retry?: int|null,
     *     error_event?: string|null,
     *     headers?: array<string, string|string[]>,
     *     on_error?: \Closure(Throwable): void|null
     * } $options
     */
    public function __construct(iterable|\Closure $producer, array $options = [])
    {
        parent::__construct(StatusCode::HTTP_OK, '', self::headers($options['headers'] ?? []));

        $this->heartbeat = self::nonNegativeNumber('heartbeat', $options['heartbeat'] ?? self::DEFAULT_HEARTBEAT);
        $this->maxDuration = self::nonNegativeNumber('max_duration', $options['max_duration'] ?? self::DEFAULT_MAX_DURATION);

        $retry = $options['retry'] ?? null;
        if ($retry !== null && (!is_int($retry) || $retry < 0)) {
            throw new InvalidArgumentException('SSE option "retry" must be null or a non-negative integer.');
        }
        $this->retry = $retry;

        $errorEvent = array_key_exists('error_event', $options) ? $options['error_event'] : 'error';
        if ($errorEvent !== null && (!is_string($errorEvent) || preg_match('/[\x00\r\n]/', $errorEvent) === 1)) {
            throw new InvalidArgumentException('SSE option "error_event" must be null or a string without NUL/CR/LF.');
        }
        $this->errorEvent = $errorEvent;

        $onError = $options['on_error'] ?? null;
        if ($onError !== null && !$onError instanceof \Closure) {
            throw new InvalidArgumentException('SSE option "on_error" must be a Closure or null.');
        }
        $this->onError = $onError;

        $this->producer = $producer instanceof \Closure
            ? $producer
            : static function (EmitterInterface $emitter) use ($producer): void {
                foreach ($producer as $event) {
                    if ($emitter->isClosed()) {
                        return;
                    }

                    $emitter->send($event);
                }
            };
    }

    /**
     * CGI / FPM：发送响应头后逐帧输出事件。
     */
    public function send(): void
    {
        $this->sendHeaders();

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        @set_time_limit(0);
        @ignore_user_abort(true);

        $emitter = new CgiEmitter($this->maxDuration);

        try {
            if ($this->retry !== null) {
                $emitter->retry($this->retry);
            }

            ($this->producer)($emitter);
        } catch (Throwable $e) {
            $this->report($e, $emitter);
        } finally {
            $emitter->close();
        }
    }

    public function getProducer(): \Closure
    {
        return $this->producer;
    }

    public function getHeartbeat(): int|float
    {
        return $this->heartbeat;
    }

    public function getMaxDuration(): int|float
    {
        return $this->maxDuration;
    }

    public function getRetry(): ?int
    {
        return $this->retry;
    }

    public function getErrorEvent(): ?string
    {
        return $this->errorEvent;
    }

    public function getErrorHandler(): ?\Closure
    {
        return $this->onError;
    }

    /**
     * 流中异常：记录（可选）并发送 error 事件，避免直接抛出造成响应截断。
     */
    public function report(Throwable $e, EmitterInterface $emitter): void
    {
        $this->callErrorHandler($e);

        $frame = $this->errorFrame($e);

        if ($frame === null || $emitter->isClosed()) {
            return;
        }

        try {
            $emitter->send(Event::data([
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], $this->errorEvent));
        } catch (Throwable) {
            // 错误事件本身失败时静默，避免覆盖原始异常
        }
    }

    /**
     * 调用应用注册的异常回调（仅记录，不决定是否下发错误事件）。
     */
    public function callErrorHandler(Throwable $e): void
    {
        if ($this->onError !== null) {
            ($this->onError)($e);
        }
    }

    /**
     * 生成错误事件帧；未配置 error_event 时返回 null。
     */
    public function errorFrame(Throwable $e): ?string
    {
        if ($this->errorEvent === null) {
            return null;
        }

        return Event::data([
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ], $this->errorEvent)->format();
    }

    /**
     * @param array<string, string|string[]> $headers
     * @return array<string, string|string[]>
     */
    private static function headers(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[strtolower((string) $name)] = $value;
        }

        return array_merge([
            'content-type' => 'text/event-stream; charset=UTF-8',
            'cache-control' => 'no-cache, no-transform',
            'x-accel-buffering' => 'no',
        ], $normalized);
    }

    private static function nonNegativeNumber(string $name, mixed $value): int|float
    {
        if ((!is_int($value) && !is_float($value)) || $value < 0) {
            throw new InvalidArgumentException(sprintf('SSE option "%s" must be a non-negative number.', $name));
        }

        return $value;
    }
}
