<?php
use FastD\Http\Request;

/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testBaseRequest()
    {
        $request = new Request('https://api.github.com/');

        $request->setBasicAuthentication('jan', '123456');
        $request->setReferrer('http://example.com/');

        $response = $request->send();
    }
}
