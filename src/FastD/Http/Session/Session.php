<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/22
 * Time: 下午10:50
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\Session;

use FastD\Http\Attribute\Attribute;

/**
 * Class Session
 *
 * @package FastD\Http\Session
 */
class Session extends Attribute
{
    protected $handler;

    /**
     * Constructor.
     *
     * @param SessionHandler
     */
    public function __construct(SessionHandler $sessionHandler = null)
    {
        $this->handler = $sessionHandler;

        if ($sessionHandler instanceof SessionHandler) {
            session_set_save_handler($sessionHandler, true);
        }

        session_start();

        parent::__construct($_SESSION);
    }

    /**
     * @param $name
     * @param $value
     * @param $expire
     * @return $this
     */
    public function setSession($name, $value, $expire = 3600)
    {
        if ($this->handler instanceof SessionHandler) {
            $this->handler->setTtl($expire);
        }
        $_SESSION[$name] = $value;
        return parent::set($name, $value);
    }

    /**
     * @param $name
     * @return array|int|string
     */
    public function getSession($name)
    {
        return parent::get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasSession($name)
    {
        return parent::has($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function clearSession($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }

        return isset($_SESSION[$name]) ? false : true;
    }
}
