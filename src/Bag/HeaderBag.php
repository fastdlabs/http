<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Bag;

/**
 * Class HeaderBag
 *
 * @package FastD\Http\Bag
 */
class HeaderBag extends Bag
{
    protected $alias = [];

    /**
     * HeaderBag constructor.
     *
     * @param array $bag
     */
    public function __construct(array $bag)
    {
        array_walk($bag, function ($value, $key) use (&$bag) {
            $bag[$key] = explode(',', $value);
        });

        parent::__construct($bag);
    }

    /**
     * @return null|string
     */
    public function getUserAgent()
    {
        return $this->hasGet('HTTP_USER_AGENT', null);
    }

    /**
     * @return null|string
     */
    public function getAccept()
    {
        return $this->hasGet('HTTP_ACCEPT', null);
    }

    /**
     * @return null|string
     */
    public function getAcceptEncoding()
    {
        return $this->hasGet('HTTP_ACCEPT_ENCODING', null);
    }

    /**
     * @return null|string
     */
    public function getAcceptLanguage()
    {
        return $this->hasGet('HTTP_ACCEPT_LANGUAGE', null);
    }

    /**
     * @return null|string
     */
    public function getReferer()
    {
        return $this->hasGet('HTTP_REFERER', null);
    }

    /**
     * @return null|string
     */
    public function getHost()
    {
        return $this->hasGet('HTTP_HOST', null);
    }

    /**
     * @return null|string
     */
    public function getConnection()
    {
        return $this->hasGet('HTTP_CONNECTION', null);
    }

    /**
     * @return null|string
     */
    public function getCacheControl()
    {
        return $this->hasGet('HTTP_CACHE_CONTROL', null);
    }

    /**
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        return 'xmlhttprequest' === strtolower($this->hasGet('X_REQUESTED_WITH', ''));
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        foreach ([
                     'HTTP_CLIENT_IP',
                     'HTTP_X_FORWARDED_FOR',
                     'HTTP_X_FORWARDED',
                     'HTTP_FORWARDED_FOR',
                     'HTTP_FORWARDED',
                     'HTTP_REMOTE_ADDR'
                 ] as $value) {
            if ($this->has($value)) {
                return $this->get($value);
            }
        }

        return 'unknown';
    }

    /**
     * Return http response header.
     *
     * @return string
     */
    public function __toString()
    {
        $header = '';

        foreach ($this->all() as $name => $value) {
            $header .= sprintf('%s: %s', str_replace('_', '-', $name), implode(',', $value)) . "\r\n";
        }

        return $header;
    }
}