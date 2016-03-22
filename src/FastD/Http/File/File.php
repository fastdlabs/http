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
class File extends \SplFileInfo
{
    /**
     * @var string
     */
    protected $originalName;

    /**
     * @var string
     */
    protected $originalExtension;

    /**
     * @var string
     */
    protected $hash;

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
     * @var string
     */
    protected $relativePath;

    /**
     * @return mixed
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
     * @return string
     */
    public function getOriginalExtension()
    {
        return $this->originalExtension;
    }

    /**
     * @param string $originalExtension
     * @return $this
     */
    public function setOriginalExtension($originalExtension)
    {
        $this->originalExtension = $originalExtension;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }
}