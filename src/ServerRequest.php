<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use FastD\Session\Session;
use FastD\Http\Bag\Bag;
use FastD\Http\Bag\CookieBag;
use FastD\Http\Bag\FileBag;
use FastD\Http\Bag\ServerBag;
use FastD\Session\SessionHandler;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ServerRequest
 *
 * @package FastD\Http
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    public $attributes;

    /**
     * @var CookieBag
     */
    public $cookie;

    /**
     * @var Bag
     */
    public $query;

    /**
     * @var ServerBag
     */
    public $server;

    /**
     * @var Bag
     */
    public $body;

    /**
     * @var FileBag
     */
    public $file;

    /**
     * @var Session
     */
    public $session;

    /**
     * @var mixed
     */
    protected $parsedBody;

    /**
     * @var static
     */
    protected static $requestFactory;

    /**
     * ServerRequest constructor.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     * @param array $cookie
     * @param array $server
     * @param SessionHandler|null $sessionHandler
     */
    public function __construct(
        array $get = [],
        array $post = [],
        array $files = [],
        array $cookie = [],
        array $server = [],
        SessionHandler $sessionHandler = null
    )
    {
        $this->query = new Bag($get);
        $this->body = new Bag($post);
        $this->server = new ServerBag($server);
        $this->cookie = new CookieBag($cookie);
        $this->file = new FileBag($files);

        $headers = [];
        array_walk($server, function ($value, $key) use (&$headers) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[$key] = $value;
            }
        });

        parent::__construct(sprintf('%s://%s', $this->server->getScheme(), ($this->server->getHost() . $this->server->getBaseUrl() . $this->server->getPathInfo())), $headers, 'php://input');
        $this->withMethod($this->server->getMethod());
        unset($headers);

        try {
            $sessionKey = $this->getHeaderLine(Session::SESSION_KEY);
        } catch (InvalidArgumentException $e) {
            $sessionKey = null;
        }

        $this->session = Session::start($sessionKey, $sessionHandler);
        $this->withHeader(Session::SESSION_KEY, $this->session->getSessionId());
        $this->withCookieParams([Session::SESSION_KEY => $this->session->getSessionId()]);
    }

    /**
     * @param array|null $get
     * @param array|null $post
     * @param array|null $files
     * @param array|null $cookie
     * @param array|null $server
     * @param SessionHandler|null $sessionHandler
     * @return ServerRequest
     */
    public static function createFromGlobals(
        array $get = null,
        array $post = null,
        array $files = null,
        array $cookie = null,
        array $server = null,
        SessionHandler $sessionHandler = null
    )
    {
        if (null === static::$requestFactory) {
            static::$requestFactory = new static(
                null === $get ? $_GET : [],
                null === $post ? $_POST : [],
                null === $files ? $_FILES : [],
                null === $cookie ? $_COOKIE : [],
                null === $server ? $_SERVER : [],
                $sessionHandler
            );

            if (in_array(static::$requestFactory->server->getMethod(), ['PUT', 'DELETE', 'PATCH', 'OPTIONS'])) {
                $phpInputSteam = new PhpInputStream();
                parse_str($phpInputSteam->getContents(), $post);
                static::$requestFactory->body = new Bag($post);
                unset($phpInputSteam);
            }
        }

        return static::$requestFactory;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->server->all();
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookie->all();
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        while (list($name, $value) = each($cookies)) {
            $this->cookie->set($name, $value);
        }

        return $this;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        return $this->query->all();
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     * @return static
     */
    public function withQueryParams(array $query)
    {
        while (list($name, $value) = each($cookies)) {
            $this->query->set($name, $value);
        }

        return $this;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->file->all();
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     * @return static
     * @throws InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->file->initUploadedFiles($uploadedFiles);

        return $this;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object.
     * @return static
     * @throws InvalidArgumentException if an unsupported argument type is
     *                                provided.
     */
    public function withParsedBody($data)
    {
        $this->parsedBody = $data;

        return $this;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name   The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$name];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            return $this;
        }

        unset($this->attributes[$name]);

        return $this;
    }
}