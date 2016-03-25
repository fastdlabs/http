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

namespace FastD\Http\File;


/**
 * Class Uploader
 *
 * @package FastD\Http\File\Uploaded
 */
class Upload implements UploadInterface
{
    /**
     * @var \FastD\Http\File\File[]
     */
    private $uploadedInfo;

    public function uploadTo($path)
    {
        foreach ($this->files as $file) {
            $moveFile = $this->config['path'] . DIRECTORY_SEPARATOR . $file->getHash() . '.' . $file->getOriginalExtension();

            if (!file_exists($moveFile)) {
                if (!move_uploaded_file($file->getTmpName(), $moveFile)) {
                    continue;
                }
            }

            $uploaded = new File($moveFile);
            $uploaded->setHash($file->getHash());
            $uploaded->setOriginalName($file->getName());
            $uploaded->setOriginalExtension($file->getOriginalExtension());
            $uploaded->setRelativePath(str_replace(realpath('./') . DIRECTORY_SEPARATOR, '', realpath($moveFile)));
            $this->uploadedInfo[] = $uploaded;
        }

        unset($uploaded);

        return $this;
    }

    /**
     * @return \FastD\Http\File\File[]
     */
    public function getUploadedFiles()
    {
        return $this->uploadedInfo;
    }

    /**
     * Target save directory. If dir is not exists. Tray make it.
     *
     * @param $directory
     * @return bool
     */
    public function targetDirectory($directory)
    {
        if (!is_dir($directory)) {
            if (false === mkdir($directory, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } else if (!is_writeable($directory)) {
            throw new \RuntimeException(sprintf('Unable to create the "%s" directory', $directory));
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if (empty($this->config['path'])) {
            throw new \RuntimeException('Upload file save path is cannot empty.');
        }

        foreach ($this->files as $file) {
            if ($file->getSize() > $this->config['size']) {
                throw new \RuntimeException(sprintf('The file %s size is over the range.', $file->getName()));
            }

            if (!in_array($file->getMimeType(), $this->config['exts'])) {
                throw new \RuntimeException(sprintf('The file %s extension is invalid.', $file->getMimeType()));
            }
        }

        $this->targetDirectory($this->config['path']);

        return true;
    }
}