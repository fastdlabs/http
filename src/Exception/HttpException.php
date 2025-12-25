<?php
declare(strict_types=1);

namespace FastD\Http\Exception;

use FastD\Http\Response\StatusCodeInterface;
use RuntimeException;

/**
 * Class HttpException
 * @package FastD\Http\Exception
 */
class HttpException extends RuntimeException
{
    /**
     * HttpException constructor.
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message = "Server Interval Error", protected int $statusCode = StatusCodeInterface::HTTP_INTERNAL_SERVER_ERROR)
    {
        parent::__construct($message);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
