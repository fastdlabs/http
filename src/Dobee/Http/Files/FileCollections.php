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

/**
 * Class FileCollections
 *
 * @package Dobee\Http\Files
 */
class FileCollections implements \Iterator, \Countable
{
    /**
     * @var File[]
     */
    private $files = [];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param array $files
     */
    public function __construct($name, array $files)
    {
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

        $this->name = $name;

        $this->count = count($this->files);

        unset($createFile);
    }

    /**
     * @return File[]
     */
    public function all()
    {
        return $this->files;
    }

    /**
     * @param int $index
     * @return File
     */
    public function getFile($index = 0)
    {
        return $this->files[$index];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return File
     */
    public function current()
    {
        return current($this->files);
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
        next($this->files);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return string
     */
    public function key()
    {
        return key($this->files);
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
        return isset($this->files[$this->key()]);
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
        reset($this->files);
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