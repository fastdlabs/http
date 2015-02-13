<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/28
 * Time: 下午4:52
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http;

interface ParametersInterface extends \Countable, \IteratorAggregate
{
    public function remove($key);

    public function has($key);

    public function add($key, $value);

    public function set(array $parameters = array());

    public function get($key);
}