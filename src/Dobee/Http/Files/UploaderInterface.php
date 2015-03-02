<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/2
 * Time: 下午3:26
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Files;

interface UploaderInterface
{
    public function setAllowExtensions(array $extensions);

    public function setMaxSize($maxSize);

    public function setSavePath($path);

    public function setName($name);

    public function upload();

    public function uploadRemote($url);

    public function getError();

    public function getUploadInfo();
}