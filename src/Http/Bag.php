<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:50
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http;

use Countable;
use Iterator;

/**
 * Class Attribute
 *
 * @package Attribute
 */
class Bag implements Iterator, Countable
{
    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->parameters[$name]);
        }

        return $this->has($name) ? false : true;
    }

    /**
     *
     * {@inheritdoc}
     * @param $name
     * @param bool $raw
     * @param $callback
     * @return string|int|array
     */
    public function get($name, $raw = false, $callback = null)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('Attribute %s is undefined.', $name));
        }

        $parameter = $this->parameters[$name];

        if (!$raw) {
            $parameter = $this->raw($parameter);
        }

        if (is_callable($callback)) {
            $parameter = $callback($parameter);
        }

        return $parameter;
    }

    /**
     * @param $value
     * @return string
     */
    public function raw($value)
    {
        if (is_string($value)) {
            preg_replace('/(\<script.*?\>.*?<\/script.*?\>|\<i*frame.*?\>.*?\<\/i*frame.*?\>)/ui', '', $value);
            $value = strip_tags(trim($value));
        }

        return $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * @param            $name
     * @param            $default
     * @param bool|false $raw
     * @param null       $callback
     * @return array|int|string
     */
    public function hasGet($name, $default, $raw = false, $callback = null)
    {
        try {
            return $this->get($name, $raw, $callback);
        } catch (\Exception $e) {
            return is_callable($callback) ? $callback($default) : $default;
        }
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return [] === $this->parameters;
    }

    /**
     * @return array
     */
    public function keys()
    {
        return array_keys($this->parameters);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->parameters);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->parameters);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->parameters);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->parameters[$this->key()]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->parameters);
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
        return count($this->parameters);
    }

    public function __destruct()
    {
        $this->parameters = [];
    }
}