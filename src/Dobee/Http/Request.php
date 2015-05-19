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

use Dobee\Http\Bag\CookieBag;
use Dobee\Http\Bag\FilesBag;
use Dobee\Http\Bag\HeaderBag;
use Dobee\Http\Bag\ParametersBag;
use Dobee\Http\Bag\ServerBag;
use Dobee\Http\Session\SessionHandler;
use Dobee\Http\Session\SessionBag;
use Dobee\Http\Session\SessionHandlerAbstract;

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
     * @var FilesBag
     */
    public $files;

    /**
     * @var CookieBag
     */
    public $cookies;

    /**
     * @var ServerBag
     */
    public $server;

    /**
     * @var HeaderBag
     */
    public $headers;

    /**
     * @var SessionBag
     */
    protected $session;

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
        $this->files    = new FilesBag($files);
        $this->cookies  = new CookieBag($cookie);
        $this->server   = new ServerBag($server);
        $this->headers  = new HeaderBag($this->server->getHeaders());
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
        return $this->server->getClientIp();
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->server->getRequestUri();
    }

    /**
     * @return bool|string
     */
    public function getBaseUrl()
    {
        return $this->server->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        return $this->server->getPathInfo();
    }

    /**
     * @return float
     */
    public function getRequestTimestamp()
    {
        return $this->server->getRequestTime();
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
        return $this->server->getFormat();
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
     * @param SessionHandlerAbstract $sessionHandler
     * @return SessionBag
     */
    public function getSession(SessionHandlerAbstract $sessionHandler = null)
    {
        if (null === $this->session) {
            $this->session = new SessionBag($sessionHandler);
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