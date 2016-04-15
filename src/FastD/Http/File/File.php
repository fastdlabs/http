<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 下午9:41
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\File;

/**
 * Class File
 *
 * @package FastD\Http\File
 */
class File
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
     * @var string
     */
    protected $relativePath;

    /**
     * @var static
     */
    protected $absolutePath;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $cTime;

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
     * @param mixed $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @param string $relativePath
     * @return $this
     */
    public function setRelativePath($relativePath)
    {
        $this->relativePath = $relativePath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension ?? pathinfo($this->getName(), PATHINFO_EXTENSION);
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * @param string $absolutePath
     * @return $this
     */
    public function setAbsolutePath($absolutePath)
    {
        $this->absolutePath = $absolutePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getCTime()
    {
        return $this->cTime;
    }

    /**
     * @param int $cTime
     * @return $this
     */
    public function setCTime($cTime)
    {
        $this->cTime = $cTime;

        return $this;
    }
}