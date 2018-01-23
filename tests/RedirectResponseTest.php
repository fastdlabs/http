<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */


use FastD\Http\RedirectResponse;
use FastD\Http\Response;


class RedirectResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponseRedirect()
    {
        $response = new RedirectResponse('http://examples.com');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(Response::$statusTexts[302], $response->getReasonPhrase());
        $this->assertTrue($response->isRedirection());
    }
}
