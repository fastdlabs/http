<?php
declare(strict_types=1);

namespace FastD\Http\Response;

use ArrayAccess;

/**
 * Class JsonResponse
 *
 * @package FastD\Http
 */
class JsonResponse extends Response implements ArrayAccess
{
    const JSON_OPTIONS = JSON_UNESCAPED_UNICODE;

    /**
     * Constructor.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct(array $data = [], int $status = StatusCodeInterface::HTTP_OK, array $headers = [])
    {
        $json = json_encode($data, static::JSON_OPTIONS);

        $this->withHeader('Content-Type', 'application/json; charset=UTF-8');

        parent::__construct($json, $status, $headers);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return json_decode($this->getContents(), true);
    }

    public function offsetExists(mixed $offset): bool
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet(mixed $offset): mixed
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset(mixed $offset): void
    {
        // TODO: Implement offsetUnset() method.
    }
}
