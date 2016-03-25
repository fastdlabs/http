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

namespace FastD\Http\File;

/**
 * Interface UploadedInterface
 *
 * @package FastD\Http\File\Uploaded
 */
interface UploadInterface
{
    const UPLOAD_SIZE = 4;
    const UPLOAD_EXT = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/icon',
    ];

    /**
     * @param string $path
     * @return bool
     */
    public function uploadTo($path);

    /**
     * @return bool
     */
    public function isValid();

    /**
     * @return \FastD\Http\File\File[]
     */
    public function getUploadedFiles();
}