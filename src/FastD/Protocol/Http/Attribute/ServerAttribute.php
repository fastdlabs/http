<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/12
 * Time: 下午3:58
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace FastD\Protocol\Http\Attribute;

use FastD\Protocol\Attribute\Attribute;

/**
 * Class ServerAttribute
 *
 * @package FastD\Protocol\Http\Attribute
 */
class ServerAttribute extends Attribute
{
    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * @return string
     */
    public function getRootPath()
    {
        if (null === $this->rootPath) {
            $this->rootPath = $this->getBaseUrl();
            if ('' != pathinfo($this->rootPath, PATHINFO_EXTENSION)) {
                $this->rootPath = pathinfo($this->rootPath, PATHINFO_DIRNAME);
            }
        }

        return $this->rootPath;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->has('REQUEST_FORMAT') ? $this->get('REQUEST_FORMAT') : $this->prepareFormat();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();

        foreach ($this->all() as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
        }

        return $headers;
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        if ($this->pathInfo) {
            return $this->pathInfo;
        }

        $pathInfo = $this->has('PATH_INFO') ? $this->get('PATH_INFO') : $this->preparePathInfo();

        if ('' != pathinfo($pathInfo, PATHINFO_EXTENSION)) {
            $pathInfo = substr($pathInfo, 0, strpos($pathInfo, '.'));
        }

        $this->pathInfo = $pathInfo;
        unset($pathInfo);

        return $this->pathInfo;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->has('REQUEST_URI') ? $this->get('REQUEST_URI') : $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->hasGet('SCRIPT_NAME', $this->prepareBaseUrl());
        }

        return $this->baseUrl;
    }

    /**
     * @return bool|string
     */
    public function getRequestTime()
    {
        if (!$this->has('REQUEST_TIME_FLOAT')) {
            $this->set('REQUEST_TIME_FLOAT', microtime(true));
        }

        return $this->get('REQUEST_TIME_FLOAT');
    }

    /**
     * @return bool|string
     */
    protected function prepareRequestUri()
    {
        return $this->get('PHP_SELF');
    }

    protected function prepareBaseUrl()
    {
        $filename = $this->has('SCRIPT_FILENAME') ? basename($this->get('SCRIPT_FILENAME')) : '';

        if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
            $file    = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();

        if (0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            return $baseUrl;
        }

        if (0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            return rtrim(dirname($baseUrl), '/');
        }

        $truncatedRequestUri = $requestUri;
        if (($pos = strpos($requestUri, '?')) !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl);
        if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if ((strlen($requestUri) >= strlen($baseUrl))
            && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0)))
        {
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

        if (null === $baseUrl || null === ($requestUri = $this->getRequestUri()) || $baseUrl === $requestUri) {
            return '/';
        }

        if (false !== ($pos = strpos($requestUri, '?'))) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (false === ($pathInfo = substr($requestUri, strlen($baseUrl)))) {
            return '/';
        }

        return $pathInfo;
    }

    /**
     * @return string
     */
    protected function prepareFormat()
    {
        $pathInfo = $this->has('PATH_INFO') ? $this->get('PATH_INFO') : $this->preparePathInfo();

        $format = '' == ($format = pathinfo($pathInfo, PATHINFO_EXTENSION)) ? 'php' : $format;

        $this->set('REQUEST_FORMAT', $format);

        return $format;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $value) {
            if ($this->has($value)) {
                return $this->get($value);
            }
        }

        return 'unknown';
    }
}