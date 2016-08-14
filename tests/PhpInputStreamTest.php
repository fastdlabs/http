<?php
use FastD\Http\PhpInputStream;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class PhpInputStreamTest extends PHPUnit_Framework_TestCase
{
    protected $stream;

    public function setUp()
    {
        $this->stream = new PhpInputStream();
    }

    public function testPhpInputRawData()
    {

    }
}
