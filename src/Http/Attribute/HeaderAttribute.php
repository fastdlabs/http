<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:57
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Http\Attribute;

/**
 * Class HeaderAttribute
 *
 * @package FastD\Http\Attribute
 */
class HeaderAttribute extends Attribute
{
    /**
     * @var CookiesAttribute
     */
    protected $cookies;

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
        return $this->has('HTTP_X_REQUESTED_WITH') ? 'xmlhttprequest' === strtolower($this->get('HTTP_X_REQUESTED_WITH')) : false;
    }

    public function isiOSClient()
    {
        
    }

    public function isAndroidClient()
    {

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
            $header .= sprintf('%s: %s', $name, $value) . "\r\n";
        }

        return $header;
    }
}