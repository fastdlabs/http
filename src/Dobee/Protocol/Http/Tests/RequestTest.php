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

namespace Dobee\Http\Tests;

use Dobee\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        $this->request = Request::createGlobalRequest();
    }

    public function testRequest()
    {
//        $this->assertEquals('/', $this->request->getPathInfo(true));
//
//        $this->assertEquals('/', $this->request->getBaseUrl());

        $filename = pathinfo('/me/dobee/component/http/examples/index.php', PATHINFO_EXTENSION);

        $this->assertEquals('php', $filename);

        $filename = pathinfo('/me/dobee/component/http/examples/', PATHINFO_EXTENSION);

        $this->assertEquals('', $filename);

        $filename = pathinfo('/me/dobee/component/http/examples/index', PATHINFO_EXTENSION);

        $this->assertEquals('', $filename);
    }
}