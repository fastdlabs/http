<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Response\SseResponse;
use FastD\Http\Response\StreamingResponseInterface;
use FastD\Http\Sse\EmitterInterface;
use FastD\Http\Sse\Event;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * SSE 响应对象测试：默认头、选项校验、生产者归一化与错误事件。
 */
class SseResponseTest extends TestCase
{
    public function testDefaultHeadersAndStreamingMarker(): void
    {
        $response = new SseResponse(['data']);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(StreamingResponseInterface::class, $response);
        $this->assertSame('text/event-stream; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('no-cache, no-transform', $response->getHeaderLine('Cache-Control'));
        $this->assertSame('no', $response->getHeaderLine('X-Accel-Buffering'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testCustomHeadersOverrideDefaultsCaseInsensitively(): void
    {
        $response = new SseResponse(['data'], [
            'headers' => [
                'Content-Type' => 'text/event-stream; charset=utf-8',
                'X-Custom' => 'yes',
            ],
        ]);

        $this->assertSame('text/event-stream; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('yes', $response->getHeaderLine('X-Custom'));
        $this->assertCount(1, $response->getHeader('Content-Type'));
    }

    public function testIterableProducerIsWrapped(): void
    {
        $response = new SseResponse([
            'a',
            ['b' => 2],
            Event::data('c', 'custom', '3'),
        ]);

        $emitter = $this->collectingEmitter();
        ($response->getProducer())($emitter);

        $this->assertSame([
            "data: a\n\n",
            "data: {\"b\":2}\n\n",
            "id: 3\nevent: custom\ndata: c\n\n",
        ], $emitter->frames);
    }

    public function testClosureProducerReceivesEmitter(): void
    {
        $response = new SseResponse(function (EmitterInterface $emitter): void {
            $emitter->comment('connected');
            $emitter->send(Event::data('hello', 'notice'));
        });

        $emitter = $this->collectingEmitter();
        ($response->getProducer())($emitter);

        $this->assertSame([": connected\n\n", "event: notice\ndata: hello\n\n"], $emitter->frames);
    }

    public function testEmitUsesInjectedEmitterAndRetry(): void
    {
        $response = new SseResponse(
            function (EmitterInterface $emitter): void {
                $emitter->send('hello');
            },
            ['retry' => 1500],
        );

        $emitter = $this->collectingEmitter();
        $response->emit($emitter);

        $this->assertSame(["retry: 1500\n\n", "data: hello\n\n"], $emitter->frames);
    }

    public function testOptionsAreExposed(): void
    {
        $response = new SseResponse([], [
            'heartbeat' => 5,
            'max_duration' => 60,
            'retry' => 1500,
            'error_event' => 'failure',
        ]);

        $this->assertSame(5, $response->getHeartbeat());
        $this->assertSame(60, $response->getMaxDuration());
        $this->assertSame(1500, $response->getRetry());
        $this->assertSame('failure', $response->getErrorEvent());
    }

    public function testInvalidOptionsAreRejected(): void
    {
        $cases = [
            ['heartbeat' => -1],
            ['max_duration' => -1],
            ['retry' => -1],
            ['error_event' => "bad\nevent"],
            ['headers' => 'not-an-array'],
        ];

        foreach ($cases as $options) {
            try {
                new SseResponse([], $options);
                $this->fail('Expected InvalidArgumentException for ' . json_encode($options));
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testReportEmitsErrorEventAndCallsHandler(): void
    {
        $logged = null;
        $response = new SseResponse([], [
            'on_error' => function (\Throwable $e) use (&$logged): void {
                $logged = $e;
            },
        ]);

        $emitter = $this->collectingEmitter();
        $response->report(new RuntimeException('boom', 500), $emitter);

        $this->assertInstanceOf(RuntimeException::class, $logged);
        $this->assertCount(1, $emitter->frames);
        $this->assertStringContainsString("event: error\n", $emitter->frames[0]);
        $this->assertStringContainsString('"message":"boom"', $emitter->frames[0]);
        $this->assertStringContainsString('"code":500', $emitter->frames[0]);
    }

    public function testErrorEventCanBeDisabled(): void
    {
        $response = new SseResponse([], ['error_event' => null]);
        $emitter = $this->collectingEmitter();

        $response->report(new RuntimeException('boom'), $emitter);

        $this->assertSame([], $emitter->frames);
    }

    public function testCloneKeepsProducerAndOptions(): void
    {
        $response = new SseResponse(['a'], ['heartbeat' => 7]);
        $cloned = $response->withHeader('X-Test', '1');

        $this->assertInstanceOf(SseResponse::class, $cloned);
        $this->assertSame(7, $cloned->getHeartbeat());
        $this->assertSame('1', $cloned->getHeaderLine('X-Test'));
    }

    /**
     * @return EmitterInterface&object{frames: list<string>}
     */
    private function collectingEmitter(): EmitterInterface
    {
        return new class implements EmitterInterface {
            /** @var list<string> */
            public array $frames = [];

            public function send(string|array|object $event): void
            {
                $this->frames[] = ($event instanceof Event ? $event : Event::data($event))->format();
            }

            public function comment(string $text): void
            {
                $this->frames[] = ': ' . $text . "\n\n";
            }

            public function retry(int $milliseconds): void
            {
                $this->frames[] = 'retry: ' . $milliseconds . "\n\n";
            }

            public function isClosed(): bool
            {
                return false;
            }

            public function close(): void
            {
            }
        };
    }
}
