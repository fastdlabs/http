<?php

use FastD\Http\Bag\Bag;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class BagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testBaseGetSetter()
    {
        $bag = new Bag([
            'name' => 'jan'
        ]);

        $this->assertEquals('jan', $bag->get('name'));
        $this->assertFalse($bag->hasGet('age', false));

        $bag->remove('name');
        $this->assertFalse($bag->hasGet('name', false));
        $this->assertTrue($bag->hasGet('name', false, false, function () {
            return true;
        }));

        $bag->get('test');
    }

    public function testBagRawData()
    {
        $bag = new Bag([
            'name' => '<script>alert("test");</script>'
        ]);

        $this->assertEquals($bag->get('name'), '');
    }
}
