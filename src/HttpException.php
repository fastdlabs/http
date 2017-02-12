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
     * @param string $message
     * @param int $statusCode
     */
    public function __construct($message = "Server Interval Error", $statusCode = 500)
    {
        parent::__construct($message);

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