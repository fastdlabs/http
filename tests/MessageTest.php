<?php
use FastD\Http\Message;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class MessageTest extends PHPUnit_Framework_TestCase
{
    public function testHeaders()
    {
        $message = new Message();

        $message->withHeader('name', 'jan');

        $this->assertEquals(['jan'], $message->getHeader('name'));
        $this->assertEquals('jan', $message->getHeaderLine('name'));
    }

    public function testWithHeader()
    {
        $message = new Message();

        $message
            ->withHeader('name', 'jan')
            ->withAddedHeader('name', 'janhuang')
        ;

        $this->assertEquals(['jan', 'janhuang'], $message->getHeader('name'));
        $this->assertEquals('jan,janhuang', $message->getHeaderLine('name'));
    }
}
