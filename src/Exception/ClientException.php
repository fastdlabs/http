<?php

declare(strict_types=1);

namespace FastD\Http\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;
use Throwable;

class ClientException extends RuntimeException implements ClientExceptionInterface
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}