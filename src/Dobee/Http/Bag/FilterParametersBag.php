<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/2/23
 * Time: 上午10:32
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Bag;

/**
 * Class FilterParametersBag
 *
 * @package Dobee\Http\Bag
 */
class FilterParametersBag extends ParametersBag
{
    /**
     * @return bool
     */
    public function getEmail()
    {
        $email = $this->get('email');

        if (!$this->isEmail($email)) {
            throw new \InvalidArgumentException(sprintf('Email format not validate.'));
        }

        return $email;
    }

    /**
     * @param null $email
     * @return bool
     */
    public function isEmail($email = null)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }
}