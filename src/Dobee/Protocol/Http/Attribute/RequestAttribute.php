<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:58
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\Attribute;

use Dobee\Protocol\Attribute\Attribute;

class RequestAttribute extends Attribute
{
    /*public function __construct(array $attributes = [])
    {

            // Handle PUT,DELETE request method.
            if (0 === strpos(self::$requestFactory->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                && in_array(strtoupper(self::$requestFactory->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'))
            ) {
                parse_str(self::$requestFactory->getContent(), $data);
                self::$requestFactory->request = new RequestAttribute($data);
            }
    }*/
}