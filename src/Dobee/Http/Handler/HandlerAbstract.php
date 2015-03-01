<?php
/**
 * Created by PhpStorm.
 * User: JanHuang
 * Date: 2015/3/1
 * Time: 15:27
 * Email: bboyjanhuang@gmail.com
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http\Handler;

class HandlerAbstract implements HandlerInterface
{
    protected static $handler;

    public static function createHandler()
    {
        if (null === static::$handler) {
            static::$handler = new static(func_get_args());
        }

        return static::$handler;
    }
}