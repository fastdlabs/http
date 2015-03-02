<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:19
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

use Dobee\Http\Files\FileCollections;
use Dobee\Http\Files\FilesEmptyException;
use Dobee\Http\Files\File;

/**
 * Class FilesParametersBag
 *
 * @package Dobee\Http\Bag
 */
class FilesParametersBag
{
    /**
     * @var FileCollections[]|array
     */
    private $collections = array();

    /**
     * @param null|array $files
     */
    public function __construct($files = null)
    {
        foreach ($files as $key => $file) {
            $this->collections[$key] = new FileCollections($key, $file);
        }
    }

    /**
     * @param $file_name
     * @return File
     * @throws FilesEmptyException
     */
    public function getFile($file_name)
    {
        if (0 === count($this->collections)) {
            throw new FilesEmptyException(sprintf('Not upload files.'));
        }

        $collectionName = null;
        $index = 0;
        if (false !== ($pos = strpos($file_name, '.'))) {
            $index = explode('.', $file_name);
            $collectionName = array_shift($index);
        }

        return $this->collections[$collectionName]->getFile($index);
    }
}