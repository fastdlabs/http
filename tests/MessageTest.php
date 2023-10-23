<?php
use FastD\Http\Message;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class MessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Message
     */
    protected $message;

    protected function setUp(): void
    {
        $this->message = $message = new Message();;
    }

    public function testDefaultHeader()
    {
        $this->assertEmpty($this->message->getHeaders());
    }

    public function testDefaultProtocolVersion()
    {
        $this->assertEquals('1.1', $this->message->getProtocolVersion());
    }

    public function testWithHeader()
    {
        $this->message
            ->withHeader('name', 'jan')
            ->withAddedHeader('name', 'janhuang')
        ;
        $this->assertEquals(['jan', 'janhuang'], $this->message->getHeader('name'));
        $this->assertEquals('jan,janhuang', $this->message->getHeaderLine('name'));
    }

    public function testCapitalAndSmallLetterHeaderKey()
    {
        $this->message->withHeader('AGE', 11);
        $this->assertEquals($this->message->getHeader('age'), $this->message->getHeader('AGE'));
        $this->assertEquals(11, $this->message->getHeaderLine('age'));
    }

    public function testHasHeader()
    {
        $this->assertNull($this->message->getHeaderLine('age'));
        $this->assertFalse($this->message->hasHeader('age'));
        $this->message->withHeader('age', 11);
        $this->assertTrue($this->message->hasHeader('age'));
        $this->assertTrue($this->message->hasHeader('AGE'));
    }

    public function testWithoutHeader()
    {
        $this->message->withoutHeader('age');
        $this->assertEmpty($this->message->getHeaders());
        $this->message->withHeader('age', 11);
        $this->assertTrue($this->message->hasHeader('age'));
        $this->message->withoutHeader('age');
        $this->assertNotTrue($this->message->hasHeader('age'));
        $this->assertEmpty($this->message->getHeaders());
    }
}
