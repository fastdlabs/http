<?php

declare(strict_types=1);

namespace FastD\Http\Sse;

/**
 * 流式事件发送器契约。
 *
 * 由运行时实现：CGI 直接写入输出缓冲，Swoole 写入共享内存通道后由主循环
 * 写出响应。业务代码只依赖此接口，因此同一段生产者逻辑可跨运行时复用。
 */
interface EmitterInterface
{
    /**
     * 发送一条事件。
     *
     * @param Event|string|array<array-key, mixed> $event string 作为 data；数组 JSON 编码
     */
    public function send(Event|string|array $event): void;

    /**
     * 发送注释帧（常用于心跳）。
     */
    public function comment(string $text): void;

    /**
     * 下发重连间隔（毫秒）。
     */
    public function retry(int $milliseconds): void;

    /**
     * 客户端是否已断开或流已到达上限。
     */
    public function isClosed(): bool;

    /**
     * 主动结束流。
     */
    public function close(): void;
}
