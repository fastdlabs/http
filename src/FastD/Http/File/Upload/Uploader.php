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
class Uploader
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var UploadInterface
     */
    private $uploaded;

    /**
     * @var array
     */
    private $config = [
        'path' => null,
        'ext' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/icon',
        ],
        'size' => '2M'
    ];

    /**
     * @param array $files
     * @param array $config
     */
    public function __construct(array $files, array $config)
    {
        $convertSize = function ($maxSize) {
            $max = (int)substr($maxSize, 0, -1);
            switch (strtoupper(substr($maxSize, -1))) {
                case 'T': $max *= 1024; // 1TB
                case 'G': $max *= 1024; // 1GB
                case 'M': $max *= 1024; // 1MB
                case 'K': $max *= 1024; // 1KB
            }
            unset($maxSize);
            return $max;
        };

        $this->config= array_merge($this->config, $config);
        $this->config['size'] = $convertSize($this->config['size']);
        $this->files = $files;
    }

    /**
     * @param UploadInterface $uploaded
     * @return UploadInterface
     * @throws \Exception
     */
    public function uploading(UploadInterface $uploaded = null)
    {
        if (null !== $uploaded) {
            $this->uploaded = $uploaded;
        } else {
            $this->uploaded = new Uploaded($this->config, $this->files);
        }

        try {
            $this->uploaded->isValid();
            return $this->uploaded->upload();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}