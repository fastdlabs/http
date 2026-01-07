<?php

declare(strict_types=1);

namespace FastD\Http\Exception;

use FastD\Http\Response\StatusCode;
use RuntimeException;

class HttpException extends RuntimeException
{
    /**
     * HttpException constructor.
     * @param string $reasonPhrase
     * @param int $statusCode
     */
    public function __construct(protected int $statusCode = StatusCode::HTTP_INTERNAL_SERVER_ERROR, string $reasonPhrase = "Server Interval Error", )
    {
        parent::__construct($reasonPhrase);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
