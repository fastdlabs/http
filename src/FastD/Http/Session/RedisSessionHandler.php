<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/9/2
 * Time: 下午4:07
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\Session;

class RedisSessionHandler extends SessionHandlerAbstract
{
    /**
     * @return bool
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * @param $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @param $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
    }

    /**
     * @param $save_path
     * @param $session_id
     * @return bool
     */
    public function open($save_path, $session_id)
    {

    }

    /**
     * Return session formatter string.
     *
     * @param $session_id
     * @return string
     */
    public function read($session_id)
    {
        // TODO: Implement read() method.
    }

    /**
     * @param $session_id
     * @param $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        // TODO: Implement write() method.
    }
}