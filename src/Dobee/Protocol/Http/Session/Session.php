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

namespace Dobee\Protocol\Http\Session;

use Dobee\Protocol\Attribute\Attribute;

/**
 * Class Session
 *
 * @package Dobee\Protocol\Http\Session
 */
class Session extends Attribute
{
    /**
     * Constructor.
     *
     * @param SessionHandlerAbstract $sessionHandler
     */
    public function __construct(SessionHandlerAbstract $sessionHandler = null)
    {
        if ($sessionHandler instanceof \SessionHandlerInterface) {
            session_set_save_handler($sessionHandler, true);
        }

        session_start();

        parent::__construct($_SESSION);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setSession($name, $value)
    {
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
        if (isset($_SERVER[$name])) {
            unset($_SESSION[$name]);
        }

        if (isset($_SESSION[$name])) {
            return false;
        }

        return true;
    }
}
