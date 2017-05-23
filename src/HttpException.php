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
class HttpException extends RuntimeException
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * HttpException constructor.
     * @param int $statusCode
     * @param string $message
     */
    public function __construct($statusCode = 500, $message = "Server Interval Error")
    {
        parent::__construct($message, $this->getCode());

        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}