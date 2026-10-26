<?php

declare(strict_types=1);

namespace FastD\Http\Response;

/**
 * 标记接口：流式响应。
 *
 * 实现该接口的响应不通过 PSR-7 body 输出（body 为空），而由当前运行时
 * 的流式输出通道逐帧发送；中间件（如响应缓存）应识别并透传、禁止缓存。
 */
interface StreamingResponseInterface
{
}
