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

/**
 * Class SessionHandlerAbstract
 *
 * @package Dobee\Http\Session
 */
abstract class SessionHandlerAbstract implements \SessionHandlerInterface
{
    /**
     * @return bool
     */
    abstract public function close();

    /**
     * @return bool
     */
    abstract public function destroy($session_id);

    /**
     * @return bool
     */
    abstract public function gc($maxlifetime);

    /**
     * @return bool
     */
    abstract public function open($save_path, $session_id);

    /**
     * Return session formatter string.
     *
     * @return string
     */
    abstract public function read($session_id);

    /**
     * @return bool
     */
    abstract public function write($session_id, $session_data);
}