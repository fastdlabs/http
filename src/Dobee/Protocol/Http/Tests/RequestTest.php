<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/4/2
 * Time: 下午7:07
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\Tests;

use Dobee\Protocol\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $request = new Request();

        $request->initialize(['bar' => 'foo']);
        $this->assertEquals('foo', $request->query->get('bar'));

        // filter xss
        $request->initialize([], ['bar' => 'foo', 'bar2' => '<script>alert("foo");</script>', 'bar3' => '<iframe>alert("foo");</iframe>', 'bar4' => '<frame>alert("foo");</frame>']);
        $this->assertEquals('foo', $request->request->get('bar'));
        $this->assertEquals('alert("foo");', $request->request->get('bar2'));
        $this->assertEquals('alert("foo");', $request->request->get('bar3'));
        $this->assertEquals('alert("foo");', $request->request->get('bar4'));
    }
}