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
use FastD\Http\Session\Storage\SessionStorageInterface;

/**
 * Class Session
 *
 * @package FastD\Http\Session
 */
class Session extends Attribute
{
    /**
     * @var SessionHandler
     */
    protected $handler;

    /**
     * Constructor.
     *
     * @param SessionStorageInterface $sessionStorageInterface
     */
    public function __construct(SessionStorageInterface $sessionStorageInterface = null)
    {
        if ($sessionStorageInterface instanceof SessionStorageInterface) {
            $this->handler = new SessionHandler($sessionStorageInterface);
            session_set_save_handler($this->handler, true);
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        parent::__construct($_SESSION);
    }

    /**
     * @param $name
     * @param $value
     * @param $expire
     * @return $this
     */
    public function set($name, $value, $expire = null)
    {
        $_SESSION[$name] = $value;

        return parent::set($name, $value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function clear($name)
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }

        return isset($_SESSION[$name]);
    }

    /**
     * @return $this
     */
    public function clearAll()
    {
        foreach ($this->all() as $name => $value) {
            $this->clear($name);
        }

        return $this;
    }
}
