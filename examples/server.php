<?php
/**
 *
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

include_once __DIR__ . '/../vendor/autoload.php';

echo '<pre>';
print_r(new \FastD\Http\Bag\ServerBag($_SERVER));