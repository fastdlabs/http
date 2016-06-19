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

namespace FastD\Http\File\Upload;

use FastD\Http\File\File;

/**
 * Interface UploadInterface
 *
 * @package FastD\Http\File\Upload
 */
interface UploadInterface
{
    const UPLOAD_SIZE = 4; // M

    const UPLOAD_EXT = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/icon',
    ];

    /**
     * @param File[] $files
     * @return $this
     */
    public function setFiles(array $files);

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
     * @return array
     */
    public function getErrors();

    /**
     * @return \FastD\Http\File\File[]
     */
    public function getUploadedFiles();
}