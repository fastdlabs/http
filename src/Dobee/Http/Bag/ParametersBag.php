<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:19
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

/**
 * Class ParametersBag
 *
 * @package Dobee\Http\Bag
 */
class ParametersBag implements ParametersBagInterface
{
    /**
     * @var array|null
     */
    protected $parameters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->set($parameters);
    }

    /**
     * @param $key
     * @return $this
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->parameters[$key]) ? true : false;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function add($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function set(array $parameters = array())
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param        $key
     * @param string $validate
     * @return bool
     */
    public function get($key, $validate = 'plain')
    {
        if (!$this->has($key)) {
            return false;
        }

        return $this->parameters[$key];
    }

    /**
     * @return array|null
     */
    public function all()
    {
        return $this->parameters;
    }
}