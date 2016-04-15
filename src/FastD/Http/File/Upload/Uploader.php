<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 下午12:05
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\File\Upload;

/**
 * Class Uploader
 *
 * @package FastD\Http\File\Uploaded
 */
class Uploader extends UploadAbstract
{
    /**
     * @param string $path
     * @return $this
     */
    public function uploadTo($path)
    {
        $this->isValid();

        $path = $this->targetDirectory($path);

        foreach ($this->files as $name => $file) {
            $moveFile = $path . DIRECTORY_SEPARATOR . $file->getHash() . '.' . $file->getExtension();

            if (!file_exists($moveFile)) {
                if (!move_uploaded_file($file->getTmpName(), $moveFile)) {
                    continue;
                }
            }

            $this->files[$name]->setRelativePath(str_replace(realpath('./') . DIRECTORY_SEPARATOR, '', realpath($moveFile)));
            $this->files[$name]->setAbsolutePath($moveFile);
            $this->files[$name]->setExtension($file->getExtension());
            $this->files[$name]->setType($file->getType());
            $this->files[$name]->setCTime($file->getCTime());

            unset($file);
        }

        unset($moveFile, $name);

        return $this;
    }
}