<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/28
 * Time: 下午3:48
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Protocol\Http;

/**
 * Class JsonResponse
 *
 * @package Dobee\Http
 */
class JsonResponse extends Response
{
    /**
     * Constructor.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct(array $data, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $this->headers->set('Content-Type', 'application/json; charset=utf-8;');

        $this->setContent($data);
    }
}