<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;


use RuntimeException;

/**
 * Class HttpException
 * @package FastD\Http\Exception
 */
abstract class HttpException extends RuntimeException
{
    /**
     * @return int
     */
    abstract public function getStatusCode();
}