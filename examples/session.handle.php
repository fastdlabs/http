<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 下午10:39
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

include __DIR__ . '/../vendor/autoload.php';

class SessionHandle extends \FastD\Http\Session\SessionHandlerAbstract
{
    protected $db = '';

    public function __construct()
    {
        $this->db = new PDO('mysql:host=127.0.0.1;dbname=test', 'root', '123456');

        $this->db->query('set names utf8')->execute();
    }

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
        echo $save_path;
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

$session = new \FastD\Http\Session\Session(new SessionHandle());
echo '<pre>';
print_r($session);

