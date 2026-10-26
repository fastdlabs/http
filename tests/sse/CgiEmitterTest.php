<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Sse\CgiEmitter;
use FastD\Http\Sse\Event;
use PHPUnit\Framework\TestCase;

/**
 * CGI 发送器测试：帧输出、心跳注释、重连间隔、关闭与断开/超时判定。
 */
class CgiEmitterTest extends TestCase
{
    /** @var list<string> */
    private array $frames = [];

    private function emitter(int $maxDuration = 300, ?\Closure $aborted = null, ?\Closure $clock = null): CgiEmitter
    {
        $this->frames = [];

        return new CgiEmitter(
            $maxDuration,
            function (string $frame): void {
                $this->frames[] = $frame;
            },
            $aborted ?? static fn (): bool => false,
            $clock,
        );
    }

    public function testSendAcceptsStringArrayAndEvent(): void
    {
        $emitter = $this->emitter();

        $emitter->send('hello');
        $emitter->send(['name' => 'fastd']);
        $emitter->send(new Event('done', 'done', '9'));

        $this->assertSame([
            "data: hello\n\n",
            "data: {\"name\":\"fastd\"}\n\n",
            "id: 9\nevent: done\ndata: done\n\n",
        ], $this->frames);
    }

    public function testCommentAndRetryFrames(): void
    {
        $emitter = $this->emitter();

        $emitter->comment('ping');
        $emitter->retry(3000);

        $this->assertSame([": ping\n\n", "retry: 3000\n\n"], $this->frames);
    }

    public function testCommentCannotInjectNewLines(): void
    {
        $emitter = $this->emitter();
        $emitter->comment("a\nb\rc");

        $this->assertSame([": a b c\n\n"], $this->frames);
    }

    public function testClosedEmitterDropsFrames(): void
    {
        $emitter = $this->emitter();
        $emitter->close();

        $this->assertTrue($emitter->isClosed());
        $emitter->send('ignored');
        $emitter->comment('ignored');
        $emitter->retry(1000);

        $this->assertSame([], $this->frames);
    }

    public function testAbortedConnectionClosesEmitter(): void
    {
        $emitter = $this->emitter(300, static fn (): bool => true);

        $this->assertTrue($emitter->isClosed());
        $emitter->send('ignored');
        $this->assertSame([], $this->frames);
    }

    public function testMaxDurationClosesEmitter(): void
    {
        $now = 0.0;

        $emitter = $this->emitter(10, null, static function () use (&$now): float {
            return $now;
        });

        $now = 5.0;
        $this->assertFalse($emitter->isClosed());

        $now = 11.0;
        $this->assertTrue($emitter->isClosed());
    }
}
