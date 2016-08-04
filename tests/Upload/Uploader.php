<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace Tests\Upload;

use FastD\Http\File\Upload\UploadAbstract;

class Uploader extends UploadAbstract
{
    /**
     * @param string $path
     * @return mixed
     */
    public function doUpload($path)
    {
        foreach ($this->getFiles() as $name => $file) {
            $moveFile = $path . DIRECTORY_SEPARATOR . $file->getHash() . '.' . $file->getExtension();
            if (!file_exists($moveFile)) {
                if (copy($file->getTmpName(), $moveFile)) {
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

        return $this;
    }
}