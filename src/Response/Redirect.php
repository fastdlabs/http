<?php
declare(strict_types=1);

namespace FastD\Http\Response;

use FastD\Http\Uri;

class Redirect extends Text
{
    public function __construct(string $uri, int $status = StatusCodeInterface::HTTP_FOUND, array $headers = [])
    {
        $uri = new Uri($uri);

        parent::__construct('', $status, $headers);

        $this->withHeader('Location', (string) $uri);
    }
}
