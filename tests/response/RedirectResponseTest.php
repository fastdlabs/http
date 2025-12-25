<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */


namespace response;

use FastD\Http\Response\RedirectResponse;
use FastD\Http\Response\Response;


class RedirectResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testResponseRedirect()
    {
        $response = new RedirectResponse('https://examples.com');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(Response::$statusTexts[302], $response->getReasonPhrase());
        $this->assertTrue($response->isRedirection());
    }
}
