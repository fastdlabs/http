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
 * @package FastD\Http
 */
class HeaderBag extends Bag
{
    /**
     * @var CookiesBag
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

    /**
     * @return bool
     */
    public function isiOSClient()
    {
        $agent = strtolower($this->getUserAgent());

        foreach (['iphone', 'ipad', 'itouch', 'mac', 'imac'] as $name) {
            if (false !== (strpos($agent, $name))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAndroidClient()
    {
        $agent = strtolower($this->getUserAgent());

        return false === strpos($agent, 'android') ? false : true;
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