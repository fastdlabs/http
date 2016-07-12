<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Swoole;

use FastD\Http\Session\Session;
use FastD\Storage\File\File;

/**
 * Class SwooleSession
 *
 * @package FastD\Http\Swoole
 */
class SwooleSession extends Session
{
    const PREFIX = 'fs_';

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * SwooleSession constructor.
     *
     * @param null $path
     *
     */
    public function __construct($path = null)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param $id
     * @return string
     */
    protected function setSessionId($id)
    {
        $this->sessionId = $this->path . DIRECTORY_SEPARATOR . static::PREFIX . $id;

        return $this->sessionId;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $name
     * @param $value
     * @param null $expire
     * @return $this
     */
    public function set($name, $value, $expire = null)
    {
        $sessionId = $this->setSessionId($name);

        $this->value = $value;

        return File::open($sessionId)->set(serialize($value));
    }

    /**
     * @param $name
     * @param bool $raw
     * @param null $callback
     * @return array|int|string
     */
    public function get($name, $raw = false, $callback = null)
    {
        $sessionId = $this->setSessionId($name);

        $this->value = serialize(File::open($sessionId)->get());

        return $this->value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function clear($name)
    {
        $sessionId = $this->setSessionId($name);

        if (file_exists($sessionId)) {
            unlink($sessionId);
        }
    }

    /**
     * @return $this
     */
    public function clearAll()
    {
        return -1;
    }
}