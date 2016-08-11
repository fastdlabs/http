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

/**
 * Class RedirectResponse
 *
 * @package FastD\Http
 */
class RedirectResponse extends Response
{
    public function __construct($url = '', $status = 302, $headers = array())
    {
        parent::__construct('', $status, $headers);

        $this->header->set('Location', $url);
    }
}