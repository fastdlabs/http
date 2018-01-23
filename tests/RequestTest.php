<?php
use FastD\Http\Request;
use FastD\Http\Uri;

/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testRequestUri()
    {
        $request = new Request('GET', 'http://example.com');

        $this->assertEquals($request->getUri()->getHost(), 'example.com');
        $this->assertEquals(80, $request->getUri()->getPort());
        $this->assertEquals('/', $request->getUri()->getPath());
        $this->assertEquals($request->getRequestTarget(), $request->getUri()->getPath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidRequestUri()
    {
        new Request('GET', '///');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestMethod()
    {
        $request = new Request('GET', 'http://example.com');
        $this->assertEquals('GET', $request->getMethod());
        // Test invalid method
        $request->withMethod('ABC');
    }

    public function server()
    {
        $uri = new Uri('http://www.weather.com.cn/data/cityinfo/101010100.html');

        return new Request('GET', (string) $uri);
    }

    public function testRequestTarget()
    {
        $request = $this->server();
        $response = $request->send();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testResponseWithEncoding()
    {
        $request = $this->server();

        $response = $request->send('', array(
            'Accept-Encoding: gzip'
        ));
        $this->assertEquals(substr($response->getContents(), 2, 11), 'weatherinfo');

        $response = $request->send('', array(
            'Accept-Encoding: deflate'
        ));
        $this->assertEquals(substr($response->getContents(), 2, 11), 'weatherinfo');

        $response = $request->send('', array(
            'Accept-Encoding: gzip, deflate'
        ));
        $this->assertEquals(substr($response->getContents(), 2, 11), 'weatherinfo');
    }

    public function testPostRawRequest()
    {
        $raw = '<xml><appid><![CDATA[123456789123456789]]></appid><mch_id>1234567890</mch_id><nonce_str><![CDATA[589d897212f9c]]></nonce_str><body><![CDATA[123]]></body><out_trade_no><![CDATA[runnerlee_001]]></out_trade_no><fee_type><![CDATA[CNY]]></fee_type><total_fee>1</total_fee><spbill_create_ip><![CDATA[127.0.0.1]]></spbill_create_ip><trade_type><![CDATA[NATIVE]]></trade_type><notify_url><![CDATA[http://github.com]]></notify_url><detail><![CDATA[runnerlee_test_payment]]></detail><sign><![CDATA[ZXCVBNMASDFGHJKLQWERTYUIOP123456]]></sign></xml>';
        $request = new Request('POST', 'https://api.mch.weixin.qq.com/pay/unifiedorder');
        $response = (array)simplexml_load_string($request->send($raw)->getContents(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->assertEquals('appid不存在', $response['return_msg']);
    }

    public function testWithOptions()
    {
        $request = new Request('GET', '/');
        $request->withOptions([
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => 'Hello World',
        ]);
        $this->assertEquals(
            [
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_USERAGENT => 'Hello World',
            ],
            $request->getOptions()
        );
        $request->withOptions([
            CURLOPT_CONNECTTIMEOUT => 20,
        ]);
        $this->assertEquals(
            [
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_USERAGENT => 'Hello World',
            ],
            $request->getOptions()
        );
    }
}
