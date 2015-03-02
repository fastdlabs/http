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
class File extends FilesUploader implements FileInterface
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
}