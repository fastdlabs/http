<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use FastD\Http\Bag\Bag;
use FastD\Http\Bag\CookieBag;
use FastD\Http\Bag\FileBag;
use FastD\Http\Bag\ServerBag;
use Psr\Http\Message\RequestInterface;
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
     * @var FileBag
     */
    public $file;

    /**
     * @var mixed
     */
    protected $parsedBody;

    /**
     * @var static
     */
    protected static $requestFactory;

    /**
     * The http request is has once request object.
     *
     * @param $get
     * @param $post
     * @param $files
     * @param $cookie
     * @param $server
     */
    public function __construct(
        array $get = [],
        array $post = [],
        array $files = [],
        array $cookie = [],
        array $server = []
    )
    {
        $this->query = new Bag($get);
        $this->body = new PhpInputStream();
        $this->server = new ServerBag($server);
        $this->cookie = new CookieBag($cookie);
        $this->file = new FileBag($files);

        $headers = [];
        array_walk($server, function ($value, $key) use (&$headers) {
            if (0 === strpos($value, 'HTTP_')) {
                $headers[$key] = $value;
            }
        });

        parent::__construct($this->server->getPathInfo(), $headers, 'php://input');
        $this->withMethod($this->server->getMethod());

        unset($headers);
    }

    /**
     * @param array $get
     * @param array $post
     * @param array $files
     * @param array $cookie
     * @param array $server
     * @return static
     */
    public static function createFromGlobals(
        array $get = null,
        array $post = null,
        array $files = null,
        array $cookie = null,
        array $server = null
    )
    {
        if (null === static::$requestFactory) {
            static::$requestFactory = new static(
                null === $get ? $_GET : [],
                null === $post ? $_POST : [],
                null === $files ? $_FILES : [],
                null === $cookie ? $_COOKIE : [],
                null === $server ? $_SERVER : []
            );
        }

        return static::$requestFactory;
    }

    /**
     * 处理 swoole 请求
     *
     * @param swoole_http_request $request
     * @return ServerRequest
     */
    public static function createFormSwoole(swoole_http_request $request)
    {
        $config = [
            'document_root'     => realpath('.'),
            'script_name'       => __FILE__,
        ];

        $config['script_filename'] = str_replace('//', '/', $config['document_root'] . '/' . $config['script_name']); // Equal nginx fastcgi_params $document_root$fastcgi_script_name;

        $get        = isset($request->get) ? $request->get : [];
        $post       = isset($request->post) ? $request->post : [];
        $cookie     = isset($request->cookie) ? $request->cookie : [];
        $files      = isset($request->files) ? $request->files : [];
        $server     = (function (\swoole_http_request $request, $config) {
            return [
                // Server
                'REQUEST_METHOD'    => $request->server['request_method'],
                'REQUEST_URI'       => $request->server['request_uri'],
                'PATH_INFO'         => $request->server['path_info'],
                'REQUEST_TIME'      => $request->server['request_time'],
                'GATEWAY_INTERFACE' => 'swoole/' . SWOOLE_VERSION,

                // Swoole and general server proxy or server configuration.
                'SERVER_PROTOCOL'   => isset($request->header['server_protocol']) ? $request->header['server_protocol'] : $request->server['server_protocol'],
                'REQUEST_SCHEMA'    => isset($request->header['request_scheme']) ? $request->header['request_scheme'] : explode('/',$request->server['server_protocol'])[0],
                'SERVER_NAME'       => isset($request->header['server_name']) ? $request->header['server_name'] : $request->header['host'],
                'SERVER_ADDR'       => isset($request->header['server_addr']) ? $request->header['server_addr'] : $request->header['host'],
                'SERVER_PORT'       => isset($request->header['server_port']) ? $request->header['server_port'] : $request->server['server_port'],
                'REMOTE_ADDR'       => isset($request->header['remote_addr']) ? $request->header['remote_addr'] : $request->server['remote_addr'],
                'REMOTE_PORT'       => isset($request->header['remote_port']) ? $request->header['remote_port'] : $request->server['remote_port'],
                'QUERY_STRING'      => isset($request->server['query_string']) ? $request->server['query_string'] : '',
                'DOCUMENT_ROOT'     => $config['document_root'],
                'SCRIPT_FILENAME'   => $config['script_filename'],
                'SCRIPT_NAME'       => '/' . $config['script_name'],
                'PHP_SELF'          => '/' . $config['script_name'],

                // Headers
                'HTTP_HOST'             => $request->header['host'] ?? '::1',
                'HTTP_USER_AGENT'       => $request->header['user-agent'] ?? '',
                'HTTP_ACCEPT'           => $request->header['accept'] ?? '*/*',
                'HTTP_ACCEPT_LANGUAGE'  => $request->header['accept-language'] ?? '',
                'HTTP_ACCEPT_ENCODING'  => $request->header['accept-encoding'] ?? '',
                'HTTP_CONNECTION'       => $request->header['connection'] ?? '',
                'HTTP_CACHE_CONTROL'    => isset($request->header['cache-control']) ? $request->header['cache-control'] : '',
            ];
        })($request, $config);

        return static::createFromGlobals($get, $post, $files, $cookie, $server);
    }

    /**
     * @param RequestInterface|null $request
     * @return Response
     */
    public function send(RequestInterface $request = null)
    {
        if (null === $request) {
            $request = $this;
        }

        $opts = [
            'http' => [
                'method' => $request->getMethod(),
                'header' => (string) $this->header,
            ]
        ];

        $content = file_get_contents(sprintf(
            '%s://%s%s',
            $this->getUri()->getScheme(),
            $this->getUri()->getHost(),
            $this->getUri()->getPath()
        ), false, stream_context_create($opts));

        return new Response($content, Response::HTTP_OK , false === $content ? [] :  $http_response_header);
    }

    /**
     * @return $this
     */
    public function getRequest()
    {
        return $this;
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
     * @throws \InvalidArgumentException if an invalid structure is provided.
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
     * @throws \InvalidArgumentException if an unsupported argument type is
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