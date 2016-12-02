<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Http\Factories;

use Psr\Http\Message\ServerRequestInterface;

interface ServerRequestFactoryInterface extends RequestFactoryInterface
{
    /**
     * Create a new server request from PHP globals.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals();
}