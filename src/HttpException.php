<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
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
    protected int $statusCode;

    /**
     * HttpException constructor.
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message = "Server Interval Error", int $statusCode = 500)
    {
        parent::__construct($message);

        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
