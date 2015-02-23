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

use Dobee\Http\Files\FilesInterface;
use Dobee\Http\Files\UploadFileNotExistsException;

/**
 * Class FilesParametersBag
 *
 * @package Dobee\Http\Bag
 */
class FilesParametersBag extends ParametersBag implements FilesInterface, \Countable
{
    /**
     * @var null|array
     */
    private $files = null;

    /**
     * @param null|array $files
     */
    public function __construct($files = null)
    {
        $this->files = $files;
    }

    /**
     * @return array|string
     * @throws UploadFileNotExistsException
     */
    public function getType()
    {
        if (!isset($this->files['name'])) {
            throw new UploadFileNotExistsException(sprintf('File %s is not found. Check your file upload form. If you forget setting form \'enctype\' attribute?', $file_name));
        }

        return $this->files['name'];
    }

    /**
     * @return string|array
     * @throws UploadFileNotExistsException
     */
    public function getName()
    {
        if (!isset($this->files['name'])) {
            throw new UploadFileNotExistsException(sprintf('File %s is not found. Check your file upload form. If you forget setting form \'enctype\' attribute?', $file_name));
        }

        return $this->files['name'];
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->files['size'];
    }

    /**
     * @param $file_name
     * @return FilesParametersBag
     * @throws UploadFileNotExistsException
     */
    public function getFile($file_name)
    {
        if (!$this->hasFile($file_name)) {
            throw new UploadFileNotExistsException(sprintf('File %s is not found. Check your file upload form. If you forget setting form \'enctype\' attribute?', $file_name));
        }

        return new FilesParametersBag($this->files[$file_name]);
    }

    /**
     * @param $file_name
     * @return bool
     */
    public function hasFile($file_name)
    {
        return isset($this->files[$file_name]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        if (isset($this->files['name'])) {
            return count($this->files['name']);
        }

        if (($count = count($this->files)) > 1) {
            return $count;
        }

        $files = reset($this->files);

        return count($files['name']);
    }
}