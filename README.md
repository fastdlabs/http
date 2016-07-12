# FastD Http

![Building](https://api.travis-ci.org/JanHuang/http.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/fastd/http/v/stable)](https://packagist.org/packages/fastd/http) [![Total Downloads](https://poser.pugx.org/fastd/http/downloads)](https://packagist.org/packages/fastd/http) [![Latest Unstable Version](https://poser.pugx.org/fastd/http/v/unstable)](https://packagist.org/packages/fastd/http) [![License](https://poser.pugx.org/fastd/http/license)](https://packagist.org/packages/fastd/http)

简单的 Http 协议组件, 用于解析 Http 请求信息.

如果想要创建 HTTP 请求, 则可以使用:

* [php-curl-class](https://github.com/php-curl-class/php-curl-class)
* [Goutte](https://github.com/FriendsOfPHP/Goutte)

以上库可以满足大部分 HTTP 请求处理相关工作, 支持 Swoole 处理, 具体请看项目[Swoole](https://github.com/JanHuang/swoole)。

## 要求

* php >= 7.0

## 安装

```
{
    "fastd/http": "~2.0"
}
```

## 使用

每个 Http 都是一个请求，每次上来的请求都需要有 Http 组件进行处理，但一个请求只需要处理一次，因此这里的实例化可以只需一次即可。

```php
use FastD\Http\Request;

$request = Request::createRequestHandle();
```

### session 处理

session 提供两种存储方式，默认的就是我们平时的 cookie 存储方案，另外一种的是可以将 session 存储到 `redis` 中，在获取(实例化) session 处理对象(getSessionHandle)的时候，进行注入存储对象即可。

自定义存储对象需要实现 `FastD\Http\Session\Storage\SessionStorageInterface` 接口。

例子: [session_redis.php](./examples/session_redis.php)

以我虚拟机为例:

```
host: 11.11.11.44
port: 6379
```

代码:

```
use FastD\Http\Session\Storage\SessionRedis;
use FastD\Storage\Redis\Redis;

$session = new \FastD\Http\Session\Session(new SessionRedis(new Redis([
    'host' => '11.11.11.44',
])));
```

默认使用 PHP 原生的 session 机制。

可以对 session 存储进行自定义扩展，需要实现: `FastD\Http\Session\Storage\SessionStorageInterface` 接口

### cookie 处理

cookie 处理和我们日常中的 cookie 处理 API 是一致的，使用上也并没有太大差异。

```
$cookie->set('name', 'janhuang')
```

### files 文件上传

```
$files = $request->getUploader()->uploadTo(__DIR__ . '/uploaded');
```

文件返回如下信息:

```
FastD\Http\File\File Object
(
    [name:protected] => 卡片.jpg
    [mimeType:protected] => image/jpeg
    [tmpName:protected] => /Applications/XAMPP/xamppfiles/temp/phpjbMLpI
    [size:protected] => 81086
    [error:protected] =>
    [hash:protected] => 1d5e65d3500a6c2dd6e700fc296dbfb5
    [relativePath:protected] => uploaded/1d5e65d3500a6c2dd6e700fc296dbfb5.jpg
    [absolutePath:protected] => /Users/janhuang/Documents/htdocs/me/fastd/library/http/examples/uploaded/1d5e65d3500a6c2dd6e700fc296dbfb5.jpg
    [extension:protected] => jpg
    [type:protected] => file
    [cTime:protected] => 1460730887
)
```

----

## License MIT
