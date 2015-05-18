<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/2
 * Time: 下午12:01
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Files;

/**
 * Class File
 *
 * @package Dobee\Http\Files
 */
class File implements FileInterface, \ArrayAccess
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $tmpName;

    /**
     * @var string
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $hashKey;

    /**
     * @param $name
     * @param $type
     * @param $tmpName
     * @param $size
     */
    public function __construct($name, $type, $tmpName, $size)
    {
        $this->name = $name;

        $this->type = $type;

        $this->tmpName = $tmpName;

        $this->size = $size;

        $this->hashKey = md5($name . $size);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param $type
     * @return string
     */
    public function setType($type)
    {
        return $this->type;
    }

    /**
     * @param $tmpName
     * @return $this
     */
    public function setTmpName($tmpName)
    {
        $this->tmpName = $tmpName;

        return $this;
    }

    /**
     * @return string
     */
    public function getTmpName()
    {
        return $this->tmpName;
    }

    /**
     * @return string
     */
    public function getFileHash()
    {
        return $this->hashKey;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}