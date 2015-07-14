<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/14
 * Time: 上午9:24
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Protocol\Http\Tests;

use FastD\Http\Attribute\Attribute;

class AttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testKeyValue()
    {
        $attribute = new Attribute();
        $attribute->set('name', 'janhuang');
        $attribute->set('age', 22);
        $this->assertEquals('janhuang', $attribute->get('name'));
        $this->assertEquals(22, $attribute->get('age'));
    }

    public function testInitAttribute()
    {
        $attribute = new Attribute(['name' => 'janhuang']);

        $this->assertEquals('janhuang', $attribute->get('name'));
    }

    public function testNotExists()
    {
        $attribute = new Attribute();

        $this->assertEquals('janhuang', $attribute->hasGet('name', 'janhuang'));
    }
}