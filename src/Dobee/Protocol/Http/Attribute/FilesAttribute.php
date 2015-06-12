<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:56
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\Attribute;

use Dobee\Protocol\Attribute\Attribute;
use Dobee\Protocol\Http\File\UploadFile;

/**
 * Class FilesAttribute
 *
 * @package Dobee\Protocol\Http\Attribute
 */
class FilesAttribute extends Attribute
{
    /**
     * @param array $files
     */
    public function __construct(array $files = [])
    {
        $this->initializeUploadFilesArray($files);
    }

    /**
     * @param array $files
     */
    private function initializeUploadFilesArray(array $files = [])
    {
        foreach ($files as $name => $file) {
            if (is_array($file['name'])) {
                foreach ($file['name'] as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    $this->set(sprintf('%s[%s]', $name, $key), new UploadFile($file['name'][$key], $file['type'][$key], $file['tmp_name'][$key], $file['size'][$key], $file['error'][$key]));
                }
                continue;
            }

            $this->set($name, new UploadFile($file['name'], $file['type'], $file['tmp_name'], $file['size'], $file['error']));
        }
    }
}