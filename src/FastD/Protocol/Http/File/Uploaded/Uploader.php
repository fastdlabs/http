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

namespace FastD\Protocol\Http\File\Uploaded;

/**
 * Class Uploader
 *
 * @package FastD\Protocol\Http\File\Uploaded
 */
class Uploader
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var UploadedInterface
     */
    private $uploaded;

    /**
     * @var array
     */
    private $config = [
        'save.path' => null,
        'allow.ext' => [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/icon'
        ],
        'max.size' => '2M'
    ];

    /**
     * @param array $config
     * @param array $files
     */
    public function __construct(array $config, array $files)
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
        $this->config['max.size'] = $convertSize($this->config['max.size']);
        $this->files = $files;
    }

    /**
     * @param UploadedInterface $uploaded
     * @return UploadedInterface
     * @throws \Exception
     */
    public function uploading(UploadedInterface $uploaded = null)
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