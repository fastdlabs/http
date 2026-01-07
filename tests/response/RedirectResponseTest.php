<?php

namespace response;

use FastD\Http\Response\Redirect;
use FastD\Http\Response\Text;


class RedirectResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testResponseRedirect()
    {
        $response = new Redirect('https://examples.com');
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(Text::STATUS_TEXT[302], $response->getReasonPhrase());
        $this->assertTrue($response->isRedirection());
    }
}
