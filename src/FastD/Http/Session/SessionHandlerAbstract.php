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

namespace FastD\Http\Session;

/**
 * Class SessionHandlerAbstract
 *
 * @package FastD\Http\Session
 */
abstract class SessionHandlerAbstract implements \SessionHandlerInterface
{
    /**
     * @var SessionStorageInterface
     */
    protected $storage;

    /**
     * @param SessionStorageInterface|null $sessionStorageInterface
     */
    public function __construct(SessionStorageInterface $sessionStorageInterface = null)
    {
        if (null !== $sessionStorageInterface) {
            $this->storage = $sessionStorageInterface;
        }
    }

    /**
     * @param SessionStorageInterface $sessionStorageInterface
     * @return $this
     */
    public function setStorage(SessionStorageInterface $sessionStorageInterface)
    {
        $this->storage = $sessionStorageInterface;

        return $this;
    }

    /**
     * @return SessionStorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return bool
     */
    abstract public function close();

    /**
     * @param $session_id
     * @return bool
     */
    abstract public function destroy($session_id);

    /**
     * @param $maxlifetime
     * @return bool
     */
    abstract public function gc($maxlifetime);

    /**
     * @param $save_path
     * @param $session_id
     * @return bool
     */
    abstract public function open($save_path, $session_id);

    /**
     * Return session formatter string.
     *
     * @param $session_id
     * @return string
     */
    abstract public function read($session_id);

    /**
     * @param $session_id
     * @param $session_data
     * @return bool
     */
    abstract public function write($session_id, $session_data);
}