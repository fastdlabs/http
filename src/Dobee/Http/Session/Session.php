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

namespace Dobee\Http\Session;

use Dobee\Http\Bag\ParametersBag;
use Dobee\Http\Storage\StorageInterface;

class Session extends ParametersBag
{
    private $storage;

    public function __construct(StorageInterface $storage = null)
    {
        session_start();

        $this->storage = $storage;
    }

    public function customStorage()
    {

    }
}