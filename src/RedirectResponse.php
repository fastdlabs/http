<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http;

/**
 * Class RedirectResponse
 *
 * @package FastD\Http
 */
class RedirectResponse extends Response
{
    /**
     * RedirectResponse constructor.
     *
     * @param string $url
     * @param int $status
     * @param array $headers
     */
    public function __construct($url = '', $status = 302, array $headers = [])
    {
        parent::__construct('', $status, $headers);

        $this->setLocation($url);
    }
}