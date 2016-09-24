<?php
use FastD\Http\Bag\CookieBag;

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
    /**
     * @var CookieBag
     */
    protected $cookie;

    public function setUp()
    {
        $this->cookie = new CookieBag();
    }

    public function testToStringForCookieBag()
    {
        $this->cookie->set('name', 'jan');

//        echo $this->cookie;
    }
}
