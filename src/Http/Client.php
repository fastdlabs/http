<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 16/4/15
 * Time: 下午11:04
 * Github: https://www.github.com/janhuang
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 * WebSite: http://www.janhuang.me
 */

namespace FastD\Http;

/**
 * Class Client
 *
 * @package FastD\Http
 */
class Client
{
    /**
     * @var string
     */
    protected $baseUri = '';

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * Client constructor.
     * @param string $baseUri
     */
    public function __construct($baseUri = '')
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @param $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @param $method
     * @param $pathInfo
     * @param array $params
     * @param int $timeout
     * @return Response
     */
    public function request($method, $pathInfo, array $params = [], $timeout = 3)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

        if ('GET' == strtoupper($method)) {
            $params = empty($params) ? '' : ('?' . http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            $params = '';
        }

        curl_setopt($ch, CURLOPT_URL, $this->baseUri . $pathInfo . $params);

        $content = curl_exec($ch);
        $responseHeaderInfo = curl_getinfo($ch);
        curl_close($ch);

        $responseHeaders = explode("\r\n", substr($content, 0, $responseHeaderInfo['header_size']));

        $headers = [];
        foreach ($responseHeaders as $value) {
            if (false === strpos($value, ':')) {
                continue;
            }
            list($name, $value) = explode(': ', $value);
            $headers[$name] = $value;
        }

        unset($responseHeaders);

        return new Response((substr($content, $responseHeaderInfo['header_size']) ?: ''), $responseHeaderInfo['http_code'], $headers);
    }

    /**
     * @param $path
     * @param array $params
     * @param int $timeout
     * @return Response
     */
    public function get($path, array $params = [], $timeout = 3)
    {
        return $this->request('GET', $path, $params, $timeout);
    }

    /**
     * @param $path
     * @param array $params
     * @param int $timeout
     * @return Response
     */
    public function post($path, array $params = [], $timeout = 3)
    {
        return $this->request('POST', $path, $params, $timeout);
    }

    /**
     * @param $path
     * @param array $params
     * @param int $timeout
     * @return Response
     */
    public function put($path, array $params = [], $timeout = 3)
    {
        return $this->request('PUT', $path, $params, $timeout);
    }

    /**
     * @param $path
     * @param array $params
     * @param int $timeout
     * @return Response
     */
    public function delete($path, array $params = [], $timeout = 3)
    {
        return $this->request('DELETE', $path, $params, $timeout);
    }
}