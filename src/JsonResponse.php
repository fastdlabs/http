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
    const JSON_OPTION = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

    /**
     * Constructor.
     *
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($status = 200, $headers = array())
    {
        $this->withContentType('application/json; charset=UTF-8');

        parent::__construct($status, $headers);
    }

    /**
     * @param array $content
     * @return Response
     */
    public function withContent($content)
    {
        if (is_array($content)) {
            $content = json_encode($content,static::JSON_OPTION);
        }

        return parent::withContent($content);
    }
}