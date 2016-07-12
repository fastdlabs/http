<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Swoole;

use FastD\Http\Session\Session;
use FastD\Http\Session\Storage\SessionStorageInterface;

class SwooleSession extends Session
{
    /**
     * Constructor.
     *
     * @param SessionStorageInterface $sessionStorageInterface
     */
    public function __construct(SessionStorageInterface $sessionStorageInterface = null)
    {
        
    }
}