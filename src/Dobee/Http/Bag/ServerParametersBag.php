<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:20
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

/**
 * Class ServerParametersBag
 *
 * @package Dobee\Http\Bag
 */
class ServerParametersBag extends ParametersBag
{
    /**
     * @var array
     */
    private $headers;

    /**
     * Gets the HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        if (null === $this->headers) {
            $headers = array();
            $contentHeaders = array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);
            foreach ($this->parameters as $key => $value) {
                if (0 === strpos($key, 'HTTP_')) {
                    $headers[substr($key, 5)] = $value;
                } elseif (isset($contentHeaders[$key])) {
                    $headers[$key] = $value;
                }
            }

            if (isset($this->parameters['PHP_AUTH_USER'])) {
                $headers['PHP_AUTH_USER'] = $this->parameters['PHP_AUTH_USER'];
                $headers['PHP_AUTH_PW'] = isset($this->parameters['PHP_AUTH_PW']) ? $this->parameters['PHP_AUTH_PW'] : '';
            } else {
                $authorizationHeader = null;
                if (isset($this->parameters['HTTP_AUTHORIZATION'])) {
                    $authorizationHeader = $this->parameters['HTTP_AUTHORIZATION'];
                } elseif (isset($this->parameters['REDIRECT_HTTP_AUTHORIZATION'])) {
                    $authorizationHeader = $this->parameters['REDIRECT_HTTP_AUTHORIZATION'];
                }

                if (null !== $authorizationHeader) {
                    if (0 === stripos($authorizationHeader, 'basic ')) {
                        // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                        $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
                        if (count($exploded) == 2) {
                            list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                        }
                    } elseif (empty($this->parameters['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest '))) {
                        // In some circumstances PHP_AUTH_DIGEST needs to be set
                        $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                        $this->parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    }
                }
            }

            // PHP_AUTH_USER/PHP_AUTH_PW
            if (isset($headers['PHP_AUTH_USER'])) {
                $headers['AUTHORIZATION'] = 'Basic '.base64_encode($headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW']);
            } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
                $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
            }

            $this->headers = $headers;

            unset($headers);
        }

        return $this->headers;
    }

    /**
     * Filter request parameters.
     *
     * @param        $key
     * @param null $validate
     * @return string|bool
     */
    public function get($key, $validate = null)
    {
        if (!$this->has($key)) {
            return false;
        }

        return $this->parameters[$key];
    }
}