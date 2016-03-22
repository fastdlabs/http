<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 下午12:15
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\File\Uploaded;

/**
 * Interface UploadedInterface
 *
 * @package FastD\Http\File\Uploaded
 */
interface UploadedInterface
{
    /**
     * @param array $config
     * @param array $files
     */
    public function __construct(array $config, array $files);

    /**
     * @return bool
     */
    public function upload();

    /**
     * @return \FastD\Http\File\File[]
     */
    public function getUploadFiles();

    /**
     * @return bool
     */
    public function isValid();
}