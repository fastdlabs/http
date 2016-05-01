# FastD Http

![Building](https://api.travis-ci.org/JanHuang/http.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/fastd/http/v/stable)](https://packagist.org/packages/fastd/http) [![Total Downloads](https://poser.pugx.org/fastd/http/downloads)](https://packagist.org/packages/fastd/http) [![Latest Unstable Version](https://poser.pugx.org/fastd/http/v/unstable)](https://packagist.org/packages/fastd/http) [![License](https://poser.pugx.org/fastd/http/license)](https://packagist.org/packages/fastd/http)

简单的 Http 协议组件, 用于解析 Http 请求信息.

## 要求

* php >= 7.0

## 安装

```
{
    "fastd/http": "2.0.x-dev"
}
```

## 使用

每个 Http 都是一个请求，每次上来的请求都需要有 Http 组件进行处理，但一个请求只需要处理一次，因此这里的实例化可以只需一次即可。

```php
use FastD\Http\Request;

$request = Request::createRequestHandle();
```

### query string (GET) 参数处理

当用户访问 `http://examples.com/?name=jan` 链接的时候，可以通过 `query` 对象访问对应的参数。

```
$request->query->hasGet('name', null);
```

### request body (POST|PUT|DELETE) 参数处理

当用户访问 `http://examples.com/` 链接的时候，可以通过 `request` 对象访问对应的参数。

```
$request->request->hasGet('name', null);
```

### session 处理

session 提供两种存储方式，默认的就是我们平时的 cookie 存储方案，另外一种的是可以将 session 存储到 `mysql`, `redis` 中，在获取(实例化) session 处理对象(getSessionHandle)的时候，进行注入存储对象即可。

自定义存储对象需要实现 `FastD\Http\Session\Storage\SessionStorageInterface` 接口。

例子: [session_redis.php](./examples/session_redis.php)

```
$session = new \FastD\Http\Session\Session(new RedisStorage());
```

或者

```
$session = $request->getSessionHandle(new RedisStorage());
```

默认使用 PHP 原生的 session 机制

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
