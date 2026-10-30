<?php

declare(strict_types=1);

namespace FastD\Http\Sse;

/**
 * 可选能力：支持对象事件的 SSE 发送器。
 *
 * 保持 EmitterInterface::send() 的参数类型不变，避免破坏已有自定义实现；
 * 需要发送对象时使用 sendObject()。
 */
interface ObjectEmitterInterface extends EmitterInterface
{
    /**
     * 发送对象事件；对象会由 Event::data() 序列化为 JSON。
     */
    public function sendObject(object $event): void;
}
