<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:57
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\Attribute;

class HeaderAttribute extends Attribute
{
    public function __toString()
    {
        $header = '';

        foreach ($this->all() as $name => $value) {
            $header .= sprintf('%s: %s', $name, $value);
        }

        return $header;
    }
}