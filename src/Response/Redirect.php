<?php

declare(strict_types=1);

namespace FastD\Http\Response;

use FastD\Http\Uri;

class Redirect extends Text
{
    public function __construct(string $uri, int $status = StatusCode::HTTP_FOUND, array $headers = [])
    {
        $uri = new Uri($uri);

        parent::__construct('', $status, $headers);

        $this->withHeader('Location', (string) $uri);
    }
}
