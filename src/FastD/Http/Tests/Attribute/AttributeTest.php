<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/3/20
 * Time: 上午12:11
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Protocol\Http\Tests\Attribute;

use FastD\Http\Attribute\Attribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $attribute = new Attribute(['name' => 'jan']);

        $this->assertEquals(['name' => 'jan'], $attribute->all());

        $this->assertEquals('jan', $attribute->get('name'));

        $this->assertFalse($attribute->hasGet('age', false));

        $this->assertNull($attribute->hasGet('age', null));

        $this->assertFalse($attribute->has('age'));
    }

    public function testIterator()
    {
        $attribute = new Attribute(['name' => 'jan']);


    }

    public function testCount()
    {
        $attribute = new Attribute(['name' => 'jan']);

        $this->assertEquals(1, $attribute->count());

        $this->assertEquals(1, count($attribute));
    }

    public function testEmpty()
    {
        $attribute = new Attribute(['name' => 'jan']);
    }
}
