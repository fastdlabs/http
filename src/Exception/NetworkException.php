<?php

declare(strict_types=1);

namespace FastD\Http\Exception;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

class NetworkException extends ClientException implements NetworkExceptionInterface
{
    public function __construct(
        string $message,
        int $code = 0,
        \Throwable $previous = null,
        private ?RequestInterface $request = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    public function getRequest(): RequestInterface
    {
        if ($this->request === null) {
            throw new \LogicException('Request is not available');
        }
        
        return $this->request;
    }
}