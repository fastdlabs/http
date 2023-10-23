# FastD HTTP Server and Client 

[![Build Status](https://travis-ci.org/fastdlabs/http.svg?branch=master)](https://travis-ci.org/fastdlabs/http)
![Support Swoole](https://img.shields.io/badge/support-swoole-brightgreen.svg)
![Support PSR7](https://img.shields.io/badge/support-psr7-brightgreen.svg)
[![Latest Stable Version](https://poser.pugx.org/fastd/http/v/stable)](https://packagist.org/packages/fastd/http) 
[![Total Downloads](https://poser.pugx.org/fastd/http/downloads)](https://packagist.org/packages/fastd/http) 
[![Latest Unstable Version](https://poser.pugx.org/fastd/http/v/unstable)](https://packagist.org/packages/fastd/http) 
[![License](https://poser.pugx.org/fastd/http/license)](https://packagist.org/packages/fastd/http)

简单的 Http 协议组件, 用于解析 Http 请求信息, 实现 PSR-7 标准, **支持 Swoole 扩展**.

以上库可以满足大部分 HTTP 请求处理相关工作, 支持 Swoole 处理, 具体请看项目[Swoole](https://github.com/JanHuang/swoole)。

## 要求

* php >= 7.4

## 安装

```
composer require "fastd/http" -vvv
```

## 文档

[文档](docs/readme.md)

## 使用

HTTP 组件封装了常用的服务端解释,客户端请求,并且友好集成 Swoole Http Server 解析，实现PSR-7。

HTTP 组件没有对 Session 进行封装, 如果想在项目中支持 Session, 可以通过 [Session](https://github.com/JanHuang/session) 组件进行扩展. 

##### 获取 pathinfo

```php
use FastD\Http\ServerRequest;

$request = ServerRequest::createServerRequestFromGlobals();

$request->getUri()->getPath();
```

##### Swoole Http 服务器

```php
$http = new swoole_http_server("127.0.0.1", 9501);

$http->on('request', function ($request, $response) {
    $server = SwooleServerRequest::createServerRequestFromSwoole($request);
    $response->end($server->getUri()->getPath());
});

$http->start();
```

##### cURL 请求

Request 对象内部封装了 cURL 请求, 可以直接通过方法调用

```php
$request = new Request('GET', 'https://api.github.com/');

$request->setReferrer('http://example.com/');

$response = $request->send(); // FastD\Http\Response
```

响应内容会通过 `Response` 对象返回。

### 贡献

非常欢迎感兴趣，愿意参与其中，共同打造更好PHP生态，Swoole生态的开发者。

如果你乐于此，却又不知如何开始，可以试试下面这些事情：

* 在你的系统中使用，将遇到的问题 [反馈](https://github.com/JanHuang/fastD/issues)。
* 有更好的建议？欢迎联系 [bboyjanhuang@gmail.com](mailto:bboyjanhuang@gmail.com) 或 [新浪微博:编码侠](http://weibo.com/ecbboyjan)。

### 联系

如果你在使用中遇到问题，请联系: [bboyjanhuang@gmail.com](mailto:bboyjanhuang@gmail.com). 微博: [编码侠](http://weibo.com/ecbboyjan)

## License MIT
