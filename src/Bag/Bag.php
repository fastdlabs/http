<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Bag;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

class Bag
{
    /**
     * @var array
     */
    protected $bag = [];

    public function __construct(array $bag = [])
    {
        $this->bag = $bag;
    }

    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->bag[$name]);
        }

        return $this->has($name) ? false : true;
    }

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

    protected function filter($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/(\<script.*?\>.*?<\/script.*?\>|\<i*frame.*?\>.*?\<\/i*frame.*?\>)/ui', '', $value);
            $value = strip_tags(trim($value));
        }

        return $value;
    }

    public function has($name)
    {
        return isset($this->bag[$name]);
    }

    public function hasGet($name, $default, $raw = false, $callback = null)
    {
        try {
            return $this->get($name, $raw, $callback);
        } catch (Exception $e) {
            return is_callable($callback) ? $callback($default) : $default;
        }
    }

    public function set($name, $value)
    {
        $this->bag[$name] = $value;

        return $this;
    }

    public function isEmpty()
    {
        return [] === $this->bag;
    }

    public function keys()
    {
        return array_keys($this->bag);
    }

    public function __destruct()
    {
        $this->bag = [];
    }
}