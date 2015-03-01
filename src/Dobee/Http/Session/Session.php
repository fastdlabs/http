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

class Session implements SessionInterface
{
    private $name;

    private $value;

    private $sessionId;

    private $expire;

    public function __construct(
        $name,
        $value,
        $expire = 0,
        $sessionId = ''
    )
    {
        $this->name = $name;

        $this->value = $value;

        $this->expire = $expire;

        $this->sessionId = $sessionId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    public function getExpire()
    {
        return $this->expire;
    }
}