<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/4/15
 * Time: 下午10:02
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http\File\Upload;

use FastD\Http\File\File;

/**
 * Class UploadAbstract
 *
 * @package FastD\Http\File\Upload
 */
abstract class UploadAbstract implements UploadInterface
{
    /**
     * @var File[]
     */
    protected $files;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var array
     */
    protected $config = [
        'ext'   => UploadInterface::UPLOAD_EXT,
        'size'  => UploadInterface::UPLOAD_SIZE,
    ];

    /**
     * UploadAbstract constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            if (isset($this->config[$name])) {
                $this->config[$name] = $value;
            }
        }
    }

    /**
     * @return \FastD\Http\File\File[]
     */
    public function getUploadedFiles()
    {
        return $this->files;
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

        return $directory;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->files as $file) {
            if ($file->getSize() > ($this->config['size'] * 1024 * 1024)) {
                throw new \RuntimeException(sprintf('The file %s size is over the range.', $file->getName()));
            }

            if (!in_array($file->getMimeType(), $this->config['ext'])) {
                throw new \RuntimeException(sprintf('The file %s extension is invalid.', $file->getMimeType()));
            }
        }

        return true;
    }

    /**
     * @param File[] $files
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}