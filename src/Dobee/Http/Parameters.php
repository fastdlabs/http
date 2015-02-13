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

use Traversable;

class Parameters implements ParametersInterface
{
    protected $parameters;

    public function __construct(array $parameters = array())
    {
        $this->set($parameters);
    }

    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }

        return $this;
    }

    public function has($key)
    {
        return isset($this->parameters[$key]) ? true : false;
    }

    public function add($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function set(array $parameters = array())
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function get($key, $validate = 'plain')
    {
        if (!$this->has($key)) {
            return false;
        }

        return $this->parameters[$key];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        // TODO: Implement count() method.
    }
}