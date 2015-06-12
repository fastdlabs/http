<?php
/**
 * Created by PhpStorm.
 * User: janhuang
 * Date: 15/3/22
 * Time: 下午5:03
 * Github: https://www.github.com/janhuang 
 * Coding: https://www.coding.net/janhuang
 * SegmentFault: http://segmentfault.com/u/janhuang
 * Blog: http://segmentfault.com/blog/janhuang
 * Gmail: bboyjanhuang@gmail.com
 */

namespace Dobee\Http\Filter;

class Filter 
{
    const INT = 'getInt';

    const FLOAT = 'getFloat';

    const IP = 'getIP';

    const STRING = 'getString';

    const ENGLISH = 'getEnglish';

    const CHINESE = 'getChinese';

    const PLAIN = 'getPlainText';

    const TRIM = 'getTrimText';

    const LOWER = 'getLowerText';

    const UPPER = 'getUpperText';

    const EMAIL = 'getEmail';

    const URL = 'getUrl';

    const BR = 'getBr';

    const NL = 'NL';

    public static function getInt($parameter)
    {
        return filter_var($parameter, FILTER_VALIDATE_INT);
    }

    public static function getFloat($parameter)
    {
        return filter_var($parameter, FILTER_VALIDATE_FLOAT);
    }

    public static function getString($parameter)
    {
        return preg_replace('/(\<script.*?\>.*?<\/script.*?\>|\<iframe.*?\>.*?\<\/iframe.*?\>)/ui', '', $parameter);
    }

    public static function getIP($parameter)
    {
        return filter_var($parameter, FILTER_VALIDATE_IP);
    }

    public static function getEnglish($parameter)
    {
        preg_match('/([a-zA-Z]+)/', $parameter, $match);

        array_shift($match);

        return implode('', $match);
    }

    public static function getChinese($parameter)
    {

    }

    public static function getPlainText($parameter)
    {
        return strip_tags($parameter);
    }

    public static function getTrimText($parameter)
    {
        return trim($parameter);
    }

    public static function getLowerText($parameter)
    {
        return strtolower($parameter);
    }

    public static function getUpperText($parameter)
    {
        return strtoupper($parameter);
    }

    public static function getEmail($parameter)
    {
        return filter_var($parameter, FILTER_VALIDATE_EMAIL);
    }

    public static function getUrl($parameter)
    {
        return filter_var($parameter, FILTER_VALIDATE_URL);
    }

    public static function getBr($parameter)
    {
        return nl2br($parameter);
    }

    public static function getNl($parameter)
    {
        return str_replace('<br />', PHP_EOL, $parameter);
    }
}