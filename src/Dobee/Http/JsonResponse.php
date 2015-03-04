<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/1/28
 * Time: 下午3:48
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * sf: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 */

namespace Dobee\Http;

/**
 * Class JsonResponse
 *
 * @package Dobee\Http
 */
class JsonResponse extends Response
{
    /**
     * @var
     */
    protected $data;
    /**
     * @var
     */
    protected $callback;
    /**
     * @var int
     */
    protected $encodingOptions;

    /**
     * Constructor.
     *
     * @param mixed $data    The response data
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     */
    public function __construct($data = null, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new \ArrayObject();
        }

        $this->encodingOptions = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

        $this->setData($data);
    }

    /**
     * @param array $data
     * @return JsonResponse
     * @throws \Exception
     */
    public function setData($data = array())
    {
        $errorHandler = null;
        $errorHandler = set_error_handler(function () use (&$errorHandler) {
            if (JSON_ERROR_NONE !== json_last_error()) {
                return;
            }

            if ($errorHandler) {
                call_user_func_array($errorHandler, func_get_args());
            }
        });

        try {
            // Clear json_last_error()
            json_encode(null);

            $this->data = json_encode($data, $this->encodingOptions);

            restore_error_handler();
        } catch (\Exception $exception) {
            restore_error_handler();

            throw $exception;
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException($this->transformJsonError());
        }

        return $this->update();
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     *
     * @return JsonResponse
     */
    protected function update()
    {
        if (null !== $this->callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $this->callback, $this->data));
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->add('Content-Type', 'application/json');
        }

        return $this->setContent($this->data);
    }

    /**
     * @return string
     */
    private function transformJsonError()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                return 'Maximum stack depth exceeded.';

            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch.';

            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found.';

            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON.';

            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded.';

            default:
                return 'Unknown error.';
        }
    }
}