<?php
use FastD\Http\Bag\HeaderBag;

/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class HeaderBagTest extends PHPUnit_Framework_TestCase
{
    protected $_headers = [
        'HTTP_HOST' => 'localhost',
        'HTTP_CONNECTION' => 'keep-alive',
        'HTTP_CACHE_CONTROL' => 'max-age=0',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36',
        'HTTP_REFERER' => 'http://localhost/me/fastd/library/http/examples/',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, sdch',
        'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
    ];

    public function testHeaderBag()
    {
        $headerBag = new HeaderBag($this->_headers);

        $this->assertEquals($headerBag->getHost(), ['localhost']);

        $this->assertEquals($headerBag->getAccept(), explode(',', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'));
    }

    public function testPsr7Header()
    {
        $headerBag = new HeaderBag([
            'HTTP_HOST' => 'localhost',
        ]);

        $this->assertEquals($headerBag->get('HTTP_HOST'), ['localhost']);
    }

    public function testToStringFoHeaderBag()
    {
        $headerBag = new HeaderBag([
            'X-Session-Id' => '111'
        ]);

        $this->assertEquals('X-Session-Id: 111' . "\r\n", ((string) $headerBag));
    }
}
