<?php
/**
 * Created by PhpStorm.
 * User: JanHuang
 * Date: 2015/3/1
 * Time: 13:45
 * Email: bboyjanhuang@gmail.com
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http\Session;

interface SessionInterface
{
    public function setSessionId($sessionId);

    public function getSessionId();

    public function setName($name);

    public function getName();

    public function setValue($value);

    public function getValue();

    public function setExpire($expire);

    public function getExpire();

    public function clear();
}