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

/**
 * Class Session
 * 
 * @package Dobee\Http\Session
 */
class Session implements SessionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $expire;

    /**
     * @var
     */
    protected $serialized = false;

    /**
     * @param     $name
     * @param     $value
     * @param int $expire
     * @param boolean $force
     */
    public function __construct($name, $value, $expire = 0, $force = true)
    {
        $this->name     = $name;
        if (is_string($value)) {
            $this->unserialize($value);
        }
        if ($force) {
            $this->value    = $value;
            $this->expire   = $expire;
        }

        $_SESSION[$name] = $this->serialize();
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $expire
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        unset($_SESSION[$this->name]);

        if (isset($_SESSION[$this->name])) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->value)) {
            return (string)$this->value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            [
                'name'      => $this->name,
                'value'     => $this->value,
                'expire'    => $this->expire
            ]
        );
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $serialized = @unserialize($serialized);
        if ($serialized) {
            $this->serialized = true;
            $this->name     = $serialized['name'];
            $this->value    = $serialized['value'];
            $this->expire   = $serialized['expire'];
        }
    }
}
