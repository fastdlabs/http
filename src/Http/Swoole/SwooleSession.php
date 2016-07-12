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
use SebastianBergmann\CodeCoverage\Report\PHP;

/**
 * Class SwooleSession
 *
 * @package FastD\Http\Swoole
 */
class SwooleSession extends Session
{
    const SESSION_ID = 'FDS_ID';

    const SESSION_PREFIX = 'sess_';

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var string
     */
    protected $sessionFile;

    /**
     * @var string
     */
    protected $path;

    /**
     * SwooleSession constructor.
     *
     * @param \swoole_http_request $request
     * @param string $path
     *
     */
    public function __construct(\swoole_http_request $request, $path = '/tmp')
    {
        $this->path = $path;

        $this->request = $request;

        if (!isset($request->cookie[static::SESSION_ID])) {
            $sessionId = md5(password_hash(microtime(true), PASSWORD_DEFAULT));
            $request->cookie[static::SESSION_ID] = $sessionId;
        } else {
            $sessionId = $request->cookie[static::SESSION_ID];
        }
        
        $this->sessionId = $sessionId;

        $this->sessionFile = $this->path . DIRECTORY_SEPARATOR . static::SESSION_PREFIX . $this->sessionId;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param $name
     * @param $value
     * @param null $expire
     * @return $this
     */
    public function set($name, $value, $expire = null)
    {
        $this->parameters[$name] = $value;

        return File::open($this->sessionFile)->set(serialize($this->parameters));
    }

    /**
     * @param $name
     * @param bool $raw
     * @param null $callback
     * @return array|int|string
     */
    public function get($name, $raw = false, $callback = null)
    {
        $value = File::open($this->sessionFile)->get();

        $this->parameters = unserialize($value);

        return $this->parameters[$name] ?? null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function clear($name)
    {
        if (file_exists($this->sessionFile)) {
            unlink($this->sessionFile);
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