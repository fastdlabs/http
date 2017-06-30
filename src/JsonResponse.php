<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

/**
 * Class JsonResponse
 * @package FastD\Http
 */
class JsonResponse extends Response
{
    const JSON_OPTIONS = JSON_UNESCAPED_UNICODE;
    const CONTENT_TYPE = 'application/json; charset=UTF-8';

    /**
     * Constructor.
     *
     * @param array $data
     * @param int $status The response status code
     * @param array $headers An array of response headers
     * @param string $version The response protocol version
     */
    public function __construct(array $data, $status = 200, array $headers = [], $version = '1.1')
    {
        $this->withContentType(static::CONTENT_TYPE);

        parent::__construct(json_encode($data, static::JSON_OPTIONS), $status, $headers, $version);
    }
}