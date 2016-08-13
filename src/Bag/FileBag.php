<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Bag;

use FastD\Http\UploadedFile;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class FileBag
 *
 * @package FastD\Http\Bag
 */
class FileBag extends Bag
{
    /**
     * @param array $files
     */
    public function __construct(array $files)
    {
        parent::__construct($this->initializePsr7File($files));
    }

    /**
     * @param array $files
     * @return UploadedFileInterface[]
     */
    private function initializePsr7File(array $files)
    {
        $fileBag = $files;

        $recursionFileBag = function ($files, &$fileBag) use (&$recursionFileBag) {
            foreach ($files as $name => $value) {
                if (!isset($value['name']) && is_array($value)) {
                    $fileBag = &$fileBag[$name];
                    $recursionFileBag($value, $fileBag);
                }
                if (isset($value['name'])) {
                    if (is_array($value['name'])) {
                        $tmpFiles = [];
                        foreach ($value['name'] as $index => $val) {
                            $tmpFiles[] = new UploadedFile($val, $value['type'][$index], $value['tmp_name'][$index], $value['error'][$index], $value['size'][$index]);
                        }
                        $fileBag[$name] = $tmpFiles;
                        unset($tmpFiles);
                    } else {
                        $fileBag[$name] = new UploadedFile($value['name'], $value['type'], $value['tmp_name'], $value['error'], $value['size']);
                    }
                }
            }
        };

        $recursionFileBag($files, $fileBag);

        unset($recursionFileBag);

        return $fileBag;
    }

    /**
     * @return UploadedFileInterface[]
     */
    public function getFiles()
    {
        return $this->all();
    }

    /**
     * @param $name
     * @return UploadedFileInterface
     */
    public function getFile($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('Upload file "%s" is undefined.', $name));
        }

        return $this->bag[$name];
    }
}