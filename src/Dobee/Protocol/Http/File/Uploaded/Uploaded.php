<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 下午12:16
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\File\Uploaded;

use Dobee\Protocol\Http\File\File;
use Dobee\Protocol\Http\File\UploadFile;

/**
 * Class Uploaded
 *
 * @package Dobee\Protocol\Http\File\Uploaded
 */
class Uploaded implements UploadedInterface
{
    /**
     * @var array
     */
    private $uploadedInfo;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var UploadFile[]
     */
    protected $files;

    public function __construct(array $config, array $files)
    {
        $this->config = $config;
        $this->files = $files;
    }

    public function upload()
    {
        foreach ($this->files as $file) {
            $moveFile = $this->config['save.path'] . DIRECTORY_SEPARATOR . $file->getHash() . '.' . $file->getOriginalExtension();
            if (file_exists($moveFile)) {
                continue;
            }
            if (move_uploaded_file($file->getTmpName(), $moveFile)) {
                $uploadFile = new File($moveFile);
                $uploadFile->setOriginalExtension($file->getName());
                $uploadFile->setOriginalName($file->getOriginalExtension());
                $this->uploadedInfo[] = $uploadFile;
            }
        }

        return $this;
    }

    /**
     * @return \Dobee\Protocol\Http\File\File[]
     */
    public function getUploadFiles()
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
        if (empty($this->config['save.path'])) {
            throw new \RuntimeException('Upload file save path is cannot empty.');
        }

        foreach ($this->files as $file) {
            if ($file->getSize() > $this->config['max.size']) {
                throw new \RuntimeException(sprintf('The file %s size is over the range.', $file->getName()));
            }

            if (!in_array($file->getMimeType(), $this->config['allow.ext'])) {
                throw new \RuntimeException(sprintf('The file %s extension is invalid.', $file->getMimeType()));
            }
        }

        $this->targetDirectory($this->config['save.path']);

        return true;
    }
}