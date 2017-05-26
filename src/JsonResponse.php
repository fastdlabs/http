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
 *
 * @package FastD\Http
 */
class JsonResponse extends Response
{
    const JSON_OPTIONS = JSON_UNESCAPED_UNICODE;

    /**
     * Constructor.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct(array $data, $status = 200, $headers = array())
    {
        $json = json_encode($data, static::JSON_OPTIONS);

        $this->withContentType('application/json; charset=UTF-8');

        parent::__construct($json, $status, $headers);
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return json_decode($this->getContents(), true);
    }
}