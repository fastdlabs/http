<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午1:20
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

/**
 * Class ServerParametersBag
 *
 * @package Dobee\Http\Bag
 */
class ServerBag extends ParametersBag
{
    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->has('REQUEST_FORMAT') ? $this->get('REQUEST_FORMAT') : $this->prepareFormat();
    }

    /**
     * Gets the HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = array();

        foreach ($this->parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
        }

        return $headers;
    }

    /**
     * Filter request parameters.
     *
     * @param        $key
     * @param null $validate
     * @return string|bool
     */
    public function get($key, $validate = null)
    {
        if (!$this->has($key)) {
            return false;
        }

        return $this->parameters[$key];
    }

    /**
     * @return bool|mixed|string
     */
    public function getPathInfo()
    {
        $pathInfo = $this->has('PATH_INFO') ? $this->get('PATH_INFO') : $this->preparePathInfo();

        if ('' != pathinfo($pathInfo, PATHINFO_EXTENSION)) {
            $pathInfo = pathinfo($pathInfo, PATHINFO_FILENAME);
        }

        return $pathInfo;
    }

    /**
     * @return bool|string
     */
    public function getRequestUri()
    {
        return $this->has('REQUEST_URI') ? $this->get('REQUEST_URI') : $this->prepareRequestUri();
    }

    /**
     * @return bool|mixed|string
     */
    public function getBaseUrl()
    {
        if ('' == pathinfo($this->get('SCRIPT_NAME'), PATHINFO_EXTENSION)) {
            return $this->get('SCRIPT_NAME');
        }

        return pathinfo($this->get('SCRIPT_NAME'), PATHINFO_DIRNAME);
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
    public function prepareRequestUri()
    {
        return $this->get('PHP_SELF');
    }

    /**
     * @return mixed
     */
    public function preparePathInfo()
    {
        return str_replace($this->get('SCRIPT_NAME'), '', $this->get('REQUEST_URI'));
    }

    /**
     * @return string
     */
    public function prepareFormat()
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
}