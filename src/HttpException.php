<?php
declare(strict_types=1);

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

        $this->code = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
