<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Sse\Event;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * SSE 事件格式化测试。
 */
class EventTest extends TestCase
{
    public function testSimpleDataFrame(): void
    {
        $this->assertSame("data: hello\n\n", (new Event('hello'))->format());
    }

    public function testEmptyDataFrame(): void
    {
        $this->assertSame("data: \n\n", (new Event())->format());
    }

    public function testMultilineDataIsSplitAndNormalized(): void
    {
        $event = new Event("line1\r\nline2\rline3\nline4");

        $this->assertSame(
            "data: line1\ndata: line2\ndata: line3\ndata: line4\n\n",
            $event->format()
        );
    }

    public function testArrayAndObjectDataAreJsonEncodedWithoutEscapingUnicode(): void
    {
        $this->assertSame(
            "data: {\"name\":\"中文\",\"n\":1}\n\n",
            Event::data(['name' => '中文', 'n' => 1])->format()
        );

        $this->assertSame(
            "data: {\"x\":1}\n\n",
            Event::data((object) ['x' => 1])->format()
        );
    }

    public function testIdEventAndRetryFields(): void
    {
        $event = new Event('payload', 'update', '42', 3000);

        $this->assertSame("id: 42\nevent: update\nretry: 3000\ndata: payload\n\n", $event->format());
    }

    public function testEventNameIsOmittedWhenEmpty(): void
    {
        $this->assertSame("data: x\n\n", (new Event('x', ''))->format());
    }

    public function testInvalidIdIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Event('x', null, "bad\nid");
    }

    public function testInvalidEventNameIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Event('x', "bad\revent");
    }

    public function testNegativeRetryIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Event('x', null, null, -1);
    }

    public function testUnencodableDataIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Event::data(['value' => NAN]);
    }
}
