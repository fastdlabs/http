<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:20
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

class HeaderParametersBag extends ParametersBag
{
    protected $cookie;

    public function setCookies(CookieParametersBag $cookie)
    {
        $this->cookie = $cookie;

        return $this;
    }

    public function getCookies()
    {
        return $this->cookie;
    }
}