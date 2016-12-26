# FastD HTTP Client and Server

![Building](https://api.travis-ci.org/JanHuang/http.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/fastd/http/v/stable)](https://packagist.org/packages/fastd/http) [![Total Downloads](https://poser.pugx.org/fastd/http/downloads)](https://packagist.org/packages/fastd/http) [![Latest Unstable Version](https://poser.pugx.org/fastd/http/v/unstable)](https://packagist.org/packages/fastd/http) [![License](https://poser.pugx.org/fastd/http/license)](https://packagist.org/packages/fastd/http)

简单的 Http 协议组件, 用于解析 Http 请求信息, 实现 PSR-7 标准, **支持 Swoole 扩展**.

以上库可以满足大部分 HTTP 请求处理相关工作, 支持 Swoole 处理, 具体请看项目[Swoole](https://github.com/JanHuang/swoole)。

## 要求

* php >= 5.6

## 安装

```
composer require "fastd/http:3.0.x-dev" -vvv
```

## 文档

[文档](docs/readme.md)

## 使用

http 组件封装了常用的方法和对象, 分别封装在 `FastD\Http\Bag\Bag` 对象中, 实例化 `FastD\Http\ServerRequest` 对象后,

分别通过类属性 `query`, `body`, `server`, `header`, `cookie` 对对象进行获取与操作

##### 获取 pathinfo

```php
use FastD\Http\ServerRequest;

$request = ServerRequest::createFromGlobals();

$request->server->getPathInfo();
```

##### Swoole Http 服务器

```php
$http = new swoole_http_server("127.0.0.1", 9501);

$http->on('request', function ($request, $response) {
    $server = SwooleServerRequest::createFromSwoole($request, $response);
    $response->end($server->server->getPathInfo());
});

$http->start();
```

##### cURL 请求

Request 对象内部封装了 cURL 请求, 可以直接通过方法调用

```php
$request = new Request('https://api.github.com/');

$request->setReferrer('http://example.com/');

$response = $request->send(); // FastD\Http\Response
```

响应内容会通过 `Response` 对象返回。

## License MIT
