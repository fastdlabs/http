<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/5/18
 * Time: 下午11:27
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Session;

interface SessionInterface extends \Serializable
{
    public function getName();

    public function setName($name);

    public function getValue();

    public function setValue($value);

    public function getExpire();

    public function setExpire($expire);
}