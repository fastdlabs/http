<?php
declare(strict_types=1);

namespace FastD\Http;

/**
 * Class RedirectResponse
 *
 * @package FastD\Http
 */
class RedirectResponse extends Response
{
    /**
     * RedirectResponse constructor.
     *
     * @param string $uri
     * @param int $status
     * @param array $headers
     */
    public function __construct(string $uri, int $status = 302, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->withHeader('Location', $uri);
    }
}
