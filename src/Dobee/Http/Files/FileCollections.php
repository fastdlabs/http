<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/2
 * Time: 下午2:25
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Files;

use Dobee\Http\Files\FileInterface;
use Dobee\Http\Files\FilesEmptyException;

/**
 * Class FileCollections
 *
 * @package Dobee\Http\Files
 */
class FileCollections extends FilesUploader implements \Iterator, \Countable
{
    /**
     * @var array|FileInterface[]
     */
    private $files = array();

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $count;

    /**
     * @param $name
     * @param $files
     */
    public function __construct($name, $files)
    {
        $this->name = $name;

        $createFile = function ($name, $type, $tmpName, $size) {
            return new File($name, $type, $tmpName, $size);
        };

        $tmp = array();
        if (is_array($files['name'])) {
            foreach ($files['name'] as $index => $f) {
                $tmp[] = $createFile(
                    $files['name'][$index],
                    $files['type'][$index],
                    $files['tmp_name'][$index],
                    $files['size'][$index]
                );
            }
        } else {
            $tmp[] = $createFile($files['name'], $files['type'], $files['tmp_name'], $files['size']);
        }

        $this->files = $tmp;

        $this->count = count($this->files);
    }

    /**
     * @param int $index
     * @return FileInterface
     * @throws FilesEmptyException
     */
    public function getFile($index = 0)
    {
        if (is_numeric($index)) {
            if (!isset($this->files[$index])) {
                throw new FilesEmptyException(sprintf('File "%s" is undefined.', $this->getName()));
            }

            return $this->files[$index];
        }

        $files = $this->files;

        foreach ($index as $val) {
            if (!isset($files[$val])) {
                throw new FilesEmptyException(sprintf('File "%s" is undefined.', $this->getName()));
            }
            $files = $files[$val];
        }

        return $files;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        // TODO: Implement current() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        // TODO: Implement next() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        // TODO: Implement key() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        // TODO: Implement valid() method.
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
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
        return $this->count;
    }
}