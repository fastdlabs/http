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
 * @package Dobee\Http\Session
 */
class Session extends Attribute
{
    public function __construct(SessionHandlerAbstract $sessionHandlerAbstract = null)
    {
        session_start();

        if (null === $sessionHandlerAbstract) {
            parent::__construct($_SESSION);
        }
    }
}
