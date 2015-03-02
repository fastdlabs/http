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
use Dobee\Http\Session\Session;

/**
 * Class SessionParametersBag
 * @package Dobee\Http\Bag
 */
class SessionParametersBag
{
    /**
     * @var array|Session[]
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
     * @var array|SessionInterface[]
     */
    private $parameters;

    /**
     * Constructor. Initialize session storage handler.
     *
     * @param \SessionHandlerInterface $sessionHandler
     */
    public function __construct(\SessionHandlerInterface $sessionHandler = null)
    {
        $this->handler = $sessionHandler;

//        session_set_save_handler($this->handler, true);

        session_start();

        $this->parameters = $_SESSION;

        $this->sessionId = session_id();
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
        if (!isset($this->sessions[$name])) {
            if (!isset($_SESSION[$name])) {
                throw new SessionException(sprintf('Session "%s" is undefined.', $name));
            }

            $this->setSession($name, $_SESSION[$name]);
        }

        return $this->sessions[$name];
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasSession($name)
    {
        return isset($this->sessions[$name]) || $_SESSION[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function removeSession($name)
    {
        return $this->sessions[$name]->clear();
    }
}