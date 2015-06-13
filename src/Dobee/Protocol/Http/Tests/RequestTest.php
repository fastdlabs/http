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
    }
}