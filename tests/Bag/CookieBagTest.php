<?php

use FastD\Http\Bag\CookieBag;
use FastD\Http\Cookie;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class CookieBagTest extends PHPUnit_Framework_TestCase
{
    public function testCookieString()
    {
        $cookie = new Cookie('name', 'jan');

        $this->assertEquals('jan', $cookie->getValue());
        $this->assertNull($cookie->getPath());
        $this->assertNull($cookie->getDomain());
    }

    public function testCookieExpire()
    {
        $cookie = new Cookie('name', 'jan', 3600);

        $this->assertEquals($cookie->getExpire(), 3600);
    }

    public function testToStringForCookieBag()
    {
        $cookieBag = new CookieBag();

        $cookieBag->set('name', 'jan');
        $this->assertEquals(1, count($cookieBag->all()));
    }
}
