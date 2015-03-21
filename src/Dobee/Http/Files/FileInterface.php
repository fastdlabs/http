<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/22
 * Time: 下午10:51
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Files;

/**
 * Interface FileInterface
 *
 * @package Dobee\Http\Files
 */
interface FileInterface
{
    /**
     * @param $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $size
     * @return $this
     */
    public function setSize($size);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $tmpName
     * @return $this
     */
    public function setTmpName($tmpName);

    /**
     * @return string
     */
    public function getTmpName();

    /**
     * @return string
     */
    public function getFileHash();
}