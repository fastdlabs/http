<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/7/14
 * Time: 上午9:39
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Protocol\Http\Tests;

use FastD\Http\Attribute\HeaderAttribute;

class HeaderAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $header = new HeaderAttribute(['name' => 'janhuang']);

        $this->assertEquals('janhuang', $header->get('name'));
    }
}