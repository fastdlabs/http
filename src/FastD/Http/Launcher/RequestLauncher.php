<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午2:49
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\Launcher;

use FastD\Http\Response;

/**
 * Class RequestLauncher
 *
 * @package FastD\Http\Launcher
 */
class RequestLauncher
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var int
     */
    private $timeout = 5;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var string
     */
    private $format;

    /**
     * @const string
     */
    const METHOD_GET = 'GET';

    /**
     * @const string
     */
    const METHOD_POST = 'POST';

    /**
     * @const string
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @const string
     */
    const METHOD_PUT = 'PUT';

    /**
     * @const string
     */
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     * @const string
     */
    const METHOD_HEAD = 'HEAD';

    /**
     * @const string
     */
    const METHOD_TRACE = 'TRACE';

    /**
     * @const string
     */
    const METHOD_PATCH = 'PATCH';

    /**
     * @var array
     */
    protected $accepts = [
        'html'  => array('text/html', 'application/xhtml+xml'),
        'txt'   => array('text/plain'),
        'js'    => array('application/javascript', 'application/x-javascript', 'text/javascript'),
        'css'   => array('text/css'),
        'json'  => array('application/json', 'application/x-json'),
        'xml'   => array('text/xml', 'application/xml', 'application/x-xml'),
        'rdf'   => array('application/rdf+xml'),
        'atom'  => array('application/atom+xml'),
        'rss'   => array('application/rss+xml'),
        'form'  => array('application/x-www-form-urlencoded'),
    ];

    /**
     * @param        $url
     * @param array  $arguments
     * @param int    $timeout
     * @param array  $headers
     */
    public function __construct($url, array $arguments = [], $timeout = 5, array $headers = [])
    {
        $this->setUrl($url);

        $this->setArguments($arguments);

        $this->setTimeout($timeout);

        $this->setFormat('json');

        $this->setHeaders($headers);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        if (!isset($this->accepts[$format])) {
            throw new \InvalidArgumentException(sprintf('Format %s is undefined.', $format));
        }

        $this->format = $this->accepts[$format];

        return $this;
    }

    /**
     * @return Response
     */
    public function get()
    {
        $this->method = self::METHOD_GET;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function post()
    {
        $this->method = self::METHOD_POST;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function put()
    {
        $this->method = self::METHOD_PUT;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function head()
    {
        $this->method = self::METHOD_HEAD;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function patch()
    {
        $this->method = self::METHOD_PATCH;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function delete()
    {
        $this->method = self::METHOD_DELETE;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function options()
    {
        $this->method = self::METHOD_OPTIONS;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    public function trace()
    {
        $this->method = self::METHOD_TRACE;

        return $this->launchHttpRequest();
    }

    /**
     * @return Response
     */
    protected function launchHttpRequest()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->getTimeout());
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Content-type: ' . implode(',' ,$this->getFormat())], $this->getHeaders()));
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

        return new Response(substr($content, $responseHeaderInfo['header_size']), $responseHeaderInfo['http_code']);
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return string
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = http_build_query($arguments);

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}