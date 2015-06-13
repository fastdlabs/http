<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/6/13
 * Time: 下午12:16
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Protocol\Http\File\Uploaded;

class Uploaded implements UploadedInterface
{
    private $regularErrors = [];

    private $errors;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function upload()
    {

    }

    public function getUploadInfo()
    {

    }

    public function getErrorInfo()
    {

    }

    /**
     * @return bool
     */
    public function verify()
    {

    }
}