<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 下午12:59
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http;

/**
 * Class RedirectResponse
 *
 * @package Dobee\Protocol\Http
 */
class RedirectResponse extends Response
{
    public function __construct($url = '', $status = 302, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->header->set('Location', $url);
    }
}