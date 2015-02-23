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
 * Interface FilesInterface
 *
 * @package Dobee\Http\Files
 */
interface FilesInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getSize();

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $file_name
     * @return array
     */
    public function getFile($file_name);

    /**
     * @param $file_name
     * @return bool
     */
    public function hasFile($file_name);
}