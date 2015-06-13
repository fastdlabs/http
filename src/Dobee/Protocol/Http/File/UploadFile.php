<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:56
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\File;

/**
 * Class UploadFile
 *
 * @package Dobee\Protocol\File
 */
class UploadFile
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $tmpName;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $error;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @param $name
     * @param $mimeType
     * @param $tmpName
     * @param $size
     * @param int $error
     */
    public function __construct($name, $mimeType, $tmpName, $size, $error)
    {
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->tmpName = $tmpName;
        $this->hash = md5($name . $mimeType . $size);
    }

    /**
     * @return mixed
     */
    public function getOriginalExtension()
    {
        return pathinfo($this->getName(), PATHINFO_EXTENSION);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function getTmpName()
    {
        return $this->tmpName;
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
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }
}