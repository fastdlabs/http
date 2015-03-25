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

use Dobee\Http\Bag\ParametersBag;
use Dobee\Http\Bag\CookieParametersBag;
use Dobee\Http\Bag\FilesParametersBag;
use Dobee\Http\Bag\HeaderParametersBag;
use Dobee\Http\Bag\ServerParametersBag;
use Dobee\Http\Session\SessionHandler;

/**
 * Class Request
 *
 * @package Dobee\Http
 */
class Request
{
    /**
     * @var ParametersBag
     */
    public $query;

    /**
     * @var ParametersBag
     */
    public $request;

    /**
     * @var FilesParametersBag
     */
    public $files;

    /**
     * @var CookieParametersBag
     */
    public $cookies;

    /**
     * @var ServerParametersBag
     */
    public $server;

    /**
     * @var HeaderParametersBag
     */
    public $headers;

    /**
     * @var SessionHandler
     */
    protected $session;

    /**
     * @var string
     */
    private $requestUri;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $pathInfo;

    /**
     * @var string
     */
    private $content;

    /**
     * @var Request
     */
    private static $requestFactory;

    /**
     * The http request is has once request object.
     *
     * @param $get
     * @param $post
     * @param $files
     * @param $cookie
     * @param $server
     */
    private function __construct($get, $post, $files, $cookie, $server)
    {
        $this->query    = new ParametersBag($get);
        $this->request  = new ParametersBag($post);
        $this->files    = new FilesParametersBag($files);
        $this->cookies  = new CookieParametersBag($cookie);
        $this->server   = new ServerParametersBag($server);
        $this->headers  = new HeaderParametersBag($this->server->getHeaders());
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->server->get('SERVER_NAME');
    }

    /**
     * @return string
     */
    public function getHttpAndHost()
    {
        return $this->getSchema() . '://' . $this->getHost();
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return $this->server->get('REQUEST_SCHEME');
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->server->get('HTTP_USER_AGENT');
    }

    /**
     * Get user client request ip.
     *
     * @return string
     */
    public function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if(isset($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }

        if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }

        if(isset($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }

        if(isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return 'unknown';
    }

    /**
     * @param bool $isNew
     * @return string
     */
    public function getRequestUri($isNew = false)
    {
        if (null === $this->requestUri|| true === $isNew) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * @return string
     */
    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->server->has('REQUEST_URI')) {
            if (0 === strpos(($requestUri = $this->server->get('REQUEST_URI')), ($schemeAndHttpHost = $this->getHttpAndHost()))) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        }

        return $requestUri;
    }

    /**
     * @param $string
     * @param $prefix
     * @return bool
     */
    private function getUrlencodedPrefix($string, $prefix)
    {
        if (0 !== strpos(rawurldecode($string), $prefix)) {
            return false;
        }

        if (preg_match('#^(%[[:xdigit:]]{2}|.){{$len}}#', $string, $match)) {
            return $match[0];
        }

        return false;
    }

    /**
     * @return bool|string
     */
    protected function prepareBaseUrl()
    {
        $filename = basename($this->server->get('SCRIPT_FILENAME'));
        if (basename($this->server->get('SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME');
        } elseif (basename($this->server->get('PHP_SELF')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        }  else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->server->get('PHP_SELF');
            $file = $this->server->get('SCRIPT_FILENAME');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/'.$seg.$baseUrl;
                ++$index;
            } while ($last > $index && (false !== $pos = strpos($path, $baseUrl)) && 0 != $pos);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl)) {
            return $prefix;
        }

        if ($baseUrl && false !== $prefix = $this->getUrlencodedPrefix($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/');
        }

        $truncatedRequestUri = $requestUri;
        if (false !== $pos = strpos($requestUri, '?')) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos(rawurldecode($truncatedRequestUri), $basename)) {
            return rtrim(str_replace($basename, '', $baseUrl), '/');
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (strlen($requestUri) >= strlen($baseUrl) && (false !== $pos = strpos($requestUri, $baseUrl)) && $pos !== 0) {
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * @return string
     */
    protected function preparePathInfo()
    {
        $baseUrl = $this->getBaseUrl();

        $format = 'php';

        if ($this->server->has('PATH_INFO')) {
            $pathInfo = $this->server->get('PATH_INFO');
            if (false !== ($pos = strpos($pathInfo, '.'))) {
                $format = substr($pathInfo, ($pos + 1));
                $pathInfo = substr($pathInfo, 0, $pos);
            }

            $this->server->set('PATH_INFO', $pathInfo);
            $this->server->set('REQUEST_FORMAT', $format);

            return $pathInfo;
        }

        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (($pos = strpos($requestUri, '.'))) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ("" != $baseUrl && false === $pathInfo = substr($requestUri, strlen($baseUrl))) {
            return '/';
        } elseif ("" == $baseUrl) {
            return $requestUri;
        }

        if ($requestUri == $pathInfo) {
            return '/';
        }

        return $pathInfo;
    }

    /**
     * @param bool $isNew
     * @return bool|string
     */
    public function getBaseUrl($isNew = false)
    {
        if (null === $this->baseUrl || true === $isNew) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    /**
     * @param bool $isNew
     * @return string
     */
    public function getPathInfo($isNew = false)
    {
        if (null === $this->pathInfo || true === $isNew) {
            $this->pathInfo = $this->preparePathInfo();
        }

        return $this->pathInfo;
    }

    /**
     * @return float
     */
    public function getRequestTimestamp()
    {
        if (!$this->server->has('REQUEST_TIME_FLOAT')) {
            $this->server->set('REQUEST_TIME_FLOAT', microtime(true));
        }

        return $this->server->get('REQUEST_TIME_FLOAT');
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD');
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->server->get('REQUEST_FORMAT')?: 'php';
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return 'xmlhttprequest' === strtolower($this->headers->get('X-Requested-With'));
    }

    /**
     * @param $method
     * @return bool
     */
    public function isMethod($method)
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * @return SessionHandler
     */
    public function getSession()
    {
        if (null === $this->session) {
            $this->session = new SessionHandler();
        }

        return $this->session;
    }

    /**
     * @param bool $asResource
     * @return resource|string
     */
    public function getContent($asResource = false)
    {
        if (false === $this->content || (true === $asResource && null !== $this->content)) {
            throw new \LogicException('getContent() can only be called once when using the resource return type.');
        }

        if (true === $asResource) {
            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * @return Request|static
     */
    public static function createGlobalRequest()
    {
        if (null === self::$requestFactory) {
            self::$requestFactory = new static($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);

            if (0 === strpos(self::$requestFactory->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
                && in_array(strtoupper(self::$requestFactory->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'))
            ) {
                parse_str(self::$requestFactory->getContent(), $data);
                self::$requestFactory->request = new ParametersBag($data);
            }
        }

        return self::$requestFactory;
    }
}