<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午10:23
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Session;

use Dobee\Http\Handler\HandlerAbstract;

/**
 * Class SessionHandler
 * @package Dobee\Http\Session
 */
class SessionHandler extends HandlerAbstract
{
    public function __construct()
    {
        session_start();
    }

    /**
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $sessionId
     * @return SessionInterface
     */
    public function createSession($name, $value, $expire, $sessionId)
    {
        $this->saveSession($name, $value, $expire, $sessionId);
        return new Session($name, $value, $expire, $sessionId);
    }

    public function saveSession($name, $value, $expire, $sessionId)
    {
        $_SESSION[$name] = $value;
    }
}