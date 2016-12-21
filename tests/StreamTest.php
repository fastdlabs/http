<?php
use FastD\Http\Stream;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class StreamTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Stream
     */
    protected $stream;

    public function setUp()
    {
        $this->stream = new Stream('php://memory', 'wb+');
    }

    public function testStreamStatus()
    {
        $this->assertTrue($this->stream->isReadable());
        $this->assertFalse($this->stream->isWritable());
    }

    public function testToStringRetrievesFullContentsOfStream()
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->assertEquals($message, (string) $this->stream);
    }

    public function testGetContentsOfStream()
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->stream->rewind();
        $this->assertEquals($message, (string) $this->stream->getContents());
    }
}
