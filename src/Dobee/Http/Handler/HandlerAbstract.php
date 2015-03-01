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

/**
 * Class HandlerAbstract
 * @package Dobee\Http\Handler
 */
class HandlerAbstract implements HandlerInterface
{
    /**
     * @var $this
     */
    protected static $handler;

    /**
     * @return $this
     */
    public static function createHandler()
    {
        if (null === static::$handler) {
            static::$handler = new static(func_get_args());
        }

        return static::$handler;
    }

    /**
     * Destruct this handler.
     */
    public function __destruct()
    {
        static::destructHandler();
    }

    /**
     * DestructHandler.
     */
    public static function destructHandler()
    {
        static::$handler = null;
    }
}