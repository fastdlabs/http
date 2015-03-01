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

use Dobee\Http\Bag\CookieParametersBag;
use Dobee\Http\Bag\FilesParametersBag;
use Dobee\Http\Bag\HeaderParametersBag;
use Dobee\Http\Bag\QueryParametersBag;
use Dobee\Http\Bag\RequestParametersBag;
use Dobee\Http\Bag\ServerParametersBag;
use Dobee\Http\Bag\SessionParametersBag;
use Dobee\Http\Session\SessionHandler;
use Dobee\Http\Session\SessionInterface;
use Dobee\Http\Cookie\CookieInterface;

/**
 * Class Request
 *
 * @package Dobee\Http
 */
class Request
{
    /**
     * @var QueryParametersBag
     */
    public $query;

    /**
     * @var RequestParametersBag
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
     * @var SessionParametersBag
     */
    public $session;

    /**
     * @var ServerParametersBag
     */
    protected $server;

    /**
     * @var HeaderParametersBag
     */
    protected $headers;

    /**
     * @var string
     */
    private $request_uri;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string
     */
    private $path_info;

    /**
     * @var string
     */
    private $format = 'php';

    /**
     * @var string
     */
    private $content;

    /**
     * @var Request
     */
    private static $request_factory;

    /**
     * @param $get
     * @param $post
     * @param $files
     * @param $cookie
     * @param $server
     */
    private function __construct($get, $post, $files, $cookie, $server)
    {
        $this->query    = new QueryParametersBag($get);
        $this->request  = new RequestParametersBag($post);
        $this->files    = new FilesParametersBag($files);
        $this->cookies  = new CookieParametersBag($cookie);
        $this->session  = new SessionParametersBag(new SessionHandler());
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
            return $_SERVER['HTTP_X_FORWARDED_FOR'];;
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
     * @return string
     */
    public function getRequestUri()
    {
        if (null === $this->request_uri) {
            $this->request_uri = $this->prepareRequestUri();
        }

        return $this->request_uri;
    }

    /**
     * @return string
     */
    protected function prepareRequestUri()
    {
        $requestUri = '';

        if ($this->headers->has('X_ORIGINAL_URL')) {
            // IIS with Microsoft Rewrite Module
            $requestUri = $this->headers->get('X_ORIGINAL_URL');
            $this->headers->remove('X_ORIGINAL_URL');
            $this->server->remove('HTTP_X_ORIGINAL_URL');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->headers->has('X_REWRITE_URL')) {
            // IIS with ISAPI_Rewrite
            $requestUri = $this->headers->get('X_REWRITE_URL');
            $this->headers->remove('X_REWRITE_URL');
        } elseif ($this->server->get('IIS_WasUrlRewritten') == '1' && $this->server->get('UNENCODED_URL') != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path, only use URL path
            $schemeAndHttpHost = $this->getHttpAndHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?'.$this->server->get('QUERY_STRING');
            }
            $this->server->remove('ORIG_PATH_INFO');
        }

        // add parameter in server
        $this->server->add('REQUEST_URI', $requestUri);

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
        } elseif (basename($this->server->get('ORIG_SCRIPT_NAME')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->server->get('PHP_SELF', '');
            $file = $this->server->get('SCRIPT_FILENAME', '');
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

        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }

        if ($this->server->has('PATH_INFO')) {
            $this->path_info = $this->server->get('PATH_INFO');
            return $this->path_info;
        }

        $pathInfo = '/';

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (($pos = strpos($requestUri, '.'))) {
            $this->format = pathinfo($requestUri, PATHINFO_EXTENSION);
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
     * @return bool|string
     */
    public function getBaseUrl()
    {
        if (null === $this->base_url) {
            $this->base_url = $this->prepareBaseUrl();
        }

        return $this->base_url;
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        if (null === $this->path_info) {
            $this->path_info = $this->preparePathInfo();
        }

        return $this->path_info;
    }

    /**
     * @return float
     */
    public function getRequestTimestamp()
    {
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
        return $this->format;
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return 'xmlhttprequest' == strtolower($this->headers->get('X-Requested-With'));
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
     * @return QueryParametersBag
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return RequestParametersBag
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string|null $name
     * @return CookieInterface
     */
    public function getCookie($name = null)
    {
        return $this->cookies->getCookie($name);
    }

    /**
     * @param string|null $name
     * @return SessionInterface
     */
    public function getSession($name = null)
    {
        return $this->session->getSession($name);
    }

    /**
     * @return HeaderParametersBag
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return ServerParametersBag
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return FilesParametersBag
     */
    public function getFiles()
    {
        return $this->files;
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
        if (null !== self::$request_factory) {
            return self::$request_factory;
        }

        if ('cli-server' === php_sapi_name()) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $_SERVER['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $_SERVER['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        self::$request_factory = new static($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);

        if (0 === strpos(self::$request_factory->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper(self::$request_factory->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str(self::$request_factory->getContent(), $data);
            self::$request_factory->request = new RequestParametersBag($data);
        }

        return self::$request_factory;
    }
}