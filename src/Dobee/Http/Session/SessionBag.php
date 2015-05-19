<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/5/19
 * Time: 上午11:34
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Session;

/**
 * Class SessionBag
 *
 * @package Dobee\Http\Session
 */
class SessionBag implements \Iterator
{
    /**
     * @var Session[]
     */
    protected $sessions = [];

    /**
     * @param SessionHandlerAbstract $sessionHandler
     */
    public function __construct(SessionHandlerAbstract $sessionHandler = null)
    {
        if (null !== $sessionHandler) {
            session_set_save_handler($sessionHandler, true);
        }

        session_start();

        foreach ($_SESSION as $name => $value) {
            $this->setSession($name, $value, 0, false);
        }
    }

    /**
     * @param $name
     * @return Session
     */
    public function getSession($name)
    {
        if (!$this->hasSession($name)) {
            throw new \InvalidArgumentException(sprintf('Session "%s" is undefined.', $name));
        }

        return $this->sessions[$name];
    }

    /**
     * @param     $name
     * @param     $value
     * @param int $expire
     * @param boolean $force
     * @return $this
     */
    public function setSession($name, $value, $expire = 0, $force = true)
    {
        $this->sessions[$name] = new Session($name, $value, $expire, $force);

        return $this;
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
     * @return bool
     */
    public function removeSession($name)
    {
        return $this->getSession($name)->clear();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->sessions);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->sessions);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->sessions);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->sessions[$this->key()]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->sessions);
    }
}