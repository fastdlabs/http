<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Bag;

use Exception;
use InvalidArgumentException;

/**
 * Class Bag
 *
 * @package FastD\Http\Bag
 */
class Bag
{
    const FILTER = '/(\<script.*?\>.*?<\/script.*?\>|\<i*frame.*?\>.*?\<\/i*frame.*?\>)/ui';

    /**
     * @var array
     */
    protected $bag;

    /**
     * Bag constructor.
     *
     * @param array $bag
     */
    public function __construct(array $bag = null)
    {
        $this->bag = $bag;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->bag;
    }

    /**
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->bag[$name]);
        }

        return $this->has($name) ? false : true;
    }

    /**
     * @param $name
     * @param $value
     * @return $this|Bag
     */
    public function add($name, $value)
    {
        if (!$this->has($name)) {
            return $this->set($name, $value);
        }

        if (is_string($value)) {
            $value = [$value];
        }

        foreach ($value as $item) {
            $this->bag[$name][] = $item;
        }

        return $this;
    }

    /**
     * @param $name
     * @param bool $raw
     * @param null $callback
     * @return mixed|string
     */
    public function get($name, $raw = false, $callback = null)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('Bag "%s" is undefined.', $name));
        }

        $parameter = $this->bag[$name];

        if (!$raw) {
            $parameter = $this->filter($parameter);
        }

        if (is_callable($callback)) {
            $parameter = $callback($parameter);
        }

        return $parameter;
    }


    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->bag[$name]);
    }

    /**
     * @param $name
     * @param $default
     * @param bool $raw
     * @param null $callback
     * @return mixed|string
     */
    public function hasGet($name, $default, $raw = false, $callback = null)
    {
        try {
            return $this->get($name, $raw, $callback);
        } catch (Exception $e) {
            return $default;
        }
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }

        $this->bag[$name] = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return [] === $this->bag;
    }

    /**
     * @param $value
     * @return mixed|string
     */
    protected function filter($value)
    {
        if (is_string($value)) {
            $value = preg_replace(static::FILTER, '', $value);
            $value = strip_tags(trim($value));
        }

        return $value;
    }

    /**
     * Destruct all.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->bag = [];
    }
}