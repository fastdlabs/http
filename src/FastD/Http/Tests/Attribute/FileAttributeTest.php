<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/21
 * Time: 下午12:07
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Protocol\Http\Tests\Attribute;

use FastD\Http\Attribute\FilesAttribute;

class FileAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testIsEmpty()
    {
        $attribute = new FilesAttribute();

        $this->assertEmpty($attribute->all());

        $attribute = new FilesAttribute(['file' => [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'error' => 0,
            'tmp_name' => '/tmp/php/test',
            'size' => 123
        ]]);

        $this->assertFalse($attribute->isEmpty());

        $attribute = new FilesAttribute(['file' => [
            'name' => '',
            'type' => '',
            'error' => 0,
            'tmp_name' => '',
            'size' => 0
        ]]);

        $this->assertTrue($attribute->isEmpty());

        $attribute = new FilesAttribute(['file' => [
            'name' => [],
            'type' => [],
            'error' => [],
            'tmp_name' => [],
            'size' => []
        ]]);

        $this->assertTrue($attribute->isEmpty());

        $attribute = new FilesAttribute(['file' => [
            'name' => [
                'test.txt'
            ],
            'type' => [
                'text/plain',
            ],
            'error' => [
                0
            ],
            'tmp_name' => [
                '/tmp/php/test'
            ],
            'size' => [
                0
            ]
        ]]);

        $this->assertFalse($attribute->isEmpty());
    }

    public function testGetFile()
    {

    }
}