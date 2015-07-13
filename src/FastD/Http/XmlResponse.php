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

namespace FastD\Http;

class XmlResponse extends Response
{
    protected $version = '1.0';

    protected $encoding = 'utf-8';

    public function __construct(array $data, $statusCode = Response::HTTP_OK, array $headers = array())
    {
        parent::__construct('', $statusCode, $headers);

        $this->headers->set('Content-Type', 'text/xml');

        $xmlResponse = $this->buildXmlData($data);
    }

    public function buildXmlData(array $data)
    {
        $xml = <<<XML
XML;
        $content = '';

        /*return sprintf($xml,
            $this->version,
            $this->encoding,
            $this->statusCode,
            $this->statusText,

        );*/
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }
}