<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

use DateTime;
use DateTimeZone;
use FastD\Http\Bag\HeaderBag;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


/**
 * Class Response
 *
 * @package FastD\Http
 */
class Response extends Message implements ResponseInterface
{
    const HEADERS = [
        'Content-Type' => 'text/html; charset=UTF-8'
    ];

    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    /**
     * Http protocol version
     *
     * @var string
     */
    protected $version = '1.1';

    /**
     * Http response status code.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * Http response status code reason phrase.
     *
     * @var string
     */
    protected $reasonPhrase;

    /**
     * Http response charset.
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Handler',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );

    /**
     * @var Cookie[]
     */
    protected $cookies = [];

    /**
     * Response constructor.
     *
     * @param string $content
     * @param int $statusCode
     * @param array $headers
     */
    public function __construct(
        $content = '',
        $statusCode = Response::HTTP_OK,
        array $headers = []
    )
    {
        $this->withBody(new Stream('php://memory', 'wb+'));
        $this->withStatus($statusCode);
        $this->withContent($content);
        parent::__construct(array_merge(static::HEADERS, $headers));
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        if (headers_sent()) {
            return $this;
        }

        header(sprintf(
                'HTTP/%s %s %s',
                $this->getProtocolVersion(),
                $this->getStatusCode(),
                $this->getReasonPhrase()
            ), true, $this->getStatusCode());

        foreach ($this->header->all() as $name => $value) {
            header(sprintf('%s: %s', $name, implode(',', $value)), false, $this->statusCode);
        }

        foreach ($this->cookies as $cookie) {
            header(sprintf('Set-Cookie: %s', $cookie->asString()), false, $this->getStatusCode());
        }

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @return Response
     *
     * @api
     */
    public function send()
    {
        $this->sendHeaders();

        echo (string) $this->getBody();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        return $this;
    }

    /**
     * @param array $cookieParams
     * @return $this
     */
    public function withCookies(array $cookieParams)
    {
        $this->cookies = $cookieParams;

        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function withContent($content)
    {
        $this->getBody()->write($content);

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getBody()->getContents();
    }

    /**
     * @param $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->header->set('Content-Type', $contentType);

        return $this;
    }

    /**
     * @return array|int|string
     */
    public function getContentType()
    {
        return $this->header->get('Content-Type');
    }

    /**
     * @param $cacheControl
     * @return $this
     */
    public function setCacheControl($cacheControl)
    {
        $this->header->remove('Cache-Control');
        $this->header->set('Cache-Control', $cacheControl);

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getCacheControl()
    {
        return $this->header->get('Cache-Control');
    }

    /**
     * Returns the literal value of the ETag HTTP header.
     *
     * @return string|null The ETag HTTP header or null if it does not exist
     *
     * @api
     */
    public function getETag()
    {
        return $this->header->get('ETag');
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $eTag The ETag unique identifier or null to remove the header
     * @param bool $weak        Whether you want a weak ETag or not
     *
     * @return Response
     */
    public function setETag($eTag = null, $weak = false)
    {
        $this->header->remove('ETag');
        $this->header->set('ETag', (true === $weak ? 'W/' : '') . $eTag);

        return $this;
    }

    /**
     * @param $location
     * @return $this
     */
    public function setLocation($location)
    {
        $this->header->set('Location', $location);

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getLocation()
    {
        return $this->header->get('Location');
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     *
     * @return DateTime|null A DateTime instance or null if the header does not exist
     */
    public function getExpires()
    {
        try {
            return new DateTime($this->header->get('Expires'));
        } catch (InvalidArgumentException $e) {
            // according to RFC 2616 invalid date formats (e.g. "0" and "-1") must be treated as in the past
            return DateTime::createFromFormat(DATE_RFC2822, 'Sat, 01 Jan 00 00:00:00 +0000');
        }
    }

    /**
     * Sets the Expires HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param DateTime|null $date A \DateTime instance or null to remove the header
     *
     * @return $this
     */
    public function setExpires(DateTime $date = null)
    {
        $this->header->remove('Expires');
        $date->setTimezone(new DateTimeZone("PRC"));
        $this->header->set('Expires', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Returns the number of seconds after the time specified in the response's Date
     * header when the response should no longer be considered fresh.
     *
     * First, it checks for a s-maxage directive, then a max-age directive, and then it falls
     * back on an expires header. It returns null when no maximum age can be established.
     *
     * @return int|null Number of seconds
     *
     * @api
     */
    public function getMaxAge()
    {
        return $this->header->hasGet('Cache-Control', $this->getExpires());
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh.
     *
     * This methods sets the Cache-Control max-age directive.
     *
     * @param int $value Number of seconds
     *
     * @return $this
     */
    public function setMaxAge($value)
    {
        $this->header->add('Cache-Control', 'max-age=' . $value);

        return $this;
    }

    /**
     * Sets the number of seconds after which the response should no longer be considered fresh by shared caches.
     *
     * This methods sets the Cache-Control s-maxage directive.
     *
     * @param int $value Number of seconds
     *
     * @return $this
     */
    public function setSharedMaxAge($value)
    {
        $this->setCacheControl('public');

        $this->header->add('Cache-Control', 's-maxage=' . $value);

        return $this;
    }

    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     *
     * @return DateTime|null A DateTime instance or null if the header does not exist
     */
    public function getLastModified()
    {
        return $this->header->get('Last-Modified');
    }

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     *
     * @param DateTime|null $date A \DateTime instance or null to remove the header
     * @return $this
     */
    public function setLastModified(DateTime $date = null)
    {
        $this->header->remove('Last-Modified');
        $this->header->set('Last-Modified', $date->format('D, d M Y H:i:s') . ' GMT');

        return $this;
    }

    /**
     * Modifies the response so that it conforms to the rules defined for a 304 status code.
     *
     * This sets the status, removes the body, and discards any headers
     * that MUST NOT be included in 304 responses.
     *
     * @return Response
     *
     * @see http://tools.ietf.org/html/rfc2616#section-10.3.5
     */
    public function setNotModified()
    {
        $this->withStatus(static::HTTP_NOT_MODIFIED);
        $this->getBody()->write('');

        // remove headers that MUST NOT be included with 304 Not Modified responses
        foreach (array('Allow', 'Content-Encoding', 'Content-Language', 'Content-Length', 'Content-MD5', 'Content-Type', 'Last-Modified') as $header) {
            $this->header->remove($header);
        }

        return $this;
    }

    /**
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * Is response invalid?
     *
     * @param int $code
     * @return bool
     */
    public function isInvalidStatusCode($code)
    {
        return $code < 100 || $code >= 600;
    }

    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     */
    public function __toString()
    {
        return
            sprintf(
                'HTTP/%s %s %s',
                $this->getProtocolVersion(),
                $this->getStatusCode(),
                $this->getReasonPhrase()
            ) . "\r\n" .
            $this->header . "\r\n" .
            $this->getBody()->getContents();
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param int $code            The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     * @return static
     * @throws InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        if ($this->isInvalidStatusCode($code)) {
            throw new InvalidArgumentException(sprintf('Invalid status code "%s"; must be an integer between 100 and 599, inclusive', $code));
        }

        $this->statusCode = $code;

        if (null === $reasonPhrase) {
            $this->reasonPhrase = isset(self::$statusTexts[$this->statusCode]) ? self::$statusTexts[$this->statusCode] : '';
        }

        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}