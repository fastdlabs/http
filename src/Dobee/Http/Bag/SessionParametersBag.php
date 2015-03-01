<?php
/**
 * Created by PhpStorm.
 * User: JanHuang
 * Date: 2015/3/1
 * Time: 13:52
 * Email: bboyjanhuang@gmail.com
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http\Bag;

use Dobee\Http\Session\SessionException;
use Dobee\Http\Session\SessionHandler;
use Dobee\Http\Session\SessionInterface;

/**
 * Class SessionParametersBag
 * @package Dobee\Http\Bag
 */
class SessionParametersBag
{
    /**
     * @var array
     */
    private $sessions = array();

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var SessionHandler
     */
    private $handler;

    /**
     * Constructor. Initialize session storage handler.
     */
    public function __construct()
    {
        $this->createHandler();
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     */
    public function setSession($name, $value, $expire = 0)
    {
        $this->sessions[$name] = $this->handler->createSession($name, $value, $expire, $this->getSessionId());
    }

    /**
     * @param null|string $name
     * @return SessionInterface
     * @throws SessionException
     */
    public function getSession($name = null)
    {
        if (!$this->hasSession($name)) {
            throw new SessionException(sprintf('Session "%s" is undefined.', $name));
        }

        return $this->sessions[$name];
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        if (null === $this->sessionId) {
            $this->sessionId = session_id();
        }

        return $this->sessionId;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasSession($name)
    {
        return isset($this->sessions[$name]);
    }

    /**
     * @param $name
     */
    public function removeSession($name)
    {
        unset($this->sessions[$name]);
    }

    /**
     * @return SessionHandler
     */
    public function createHandler()
    {
        if (null == $this->handler) {
            $this->handler = SessionHandler::createHandler();
        }

        return $this->handler;
    }
}