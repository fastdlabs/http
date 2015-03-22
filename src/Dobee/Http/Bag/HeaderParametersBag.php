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

class HeaderParametersBag extends ParametersBag
{
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;

        header_remove('X-Powered-By');
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
}