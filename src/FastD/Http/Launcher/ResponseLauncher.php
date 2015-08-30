<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/8/30
 * Time: 下午4:24
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\Launcher;

use FastD\Http\Response;

class ResponseLauncher extends Response
{
    /**
     * Constructor.
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @api
     */
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
    }
}