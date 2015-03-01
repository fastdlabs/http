<?php
/**
 * Created by PhpStorm.
 * User: JanHuang
 * Date: 2015/3/1
 * Time: 22:20
 * Email: bboyjanhuang@gmail.com
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http\Storage;

interface StorageInterface 
{
    public function set($name, $value);

    public function get($name);

    public function remove($name);
}