<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/2/16
 * Time: 下午12:19
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Protocol\Http\Tests;

use FastD\Http\Client;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testClientParams()
    {
        $client = new Client('http://localhost/me/fastd/library/http/examples/client');

        $response = $client->get('/params.php', ['name' => 'janhuang']);

        $response = $client->post('/params.php', ['name' => 'janhuang']);

        $response = $client->put('/params.php', ['name' => 'janhuang']);

        print_r($response->getContent());
    }
}