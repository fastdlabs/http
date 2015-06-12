<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午2:57
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\Launcher\Formatter;

interface FormatInterface 
{
    public function jsonToArray();

    public function arrayToJson();

    public function jsonToSerialize();

    public function serializeToJson();

    public function serializeToArray();

    public function arrayToSerialize();
}