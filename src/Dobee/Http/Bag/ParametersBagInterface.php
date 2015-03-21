<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:18
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

/**
 * Interface ParametersBagInterface
 *
 * @package Dobee\Http\Bag
 */
interface ParametersBagInterface
{
    /**
     * @param $key
     * @return mixed
     */
    public function remove($key);

    /**
     * @param $key
     * @return mixed
     */
    public function has($key);

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @return array|null
     */
    public function all();
}