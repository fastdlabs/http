# FastD HTTP

[![Build Status](https://travis-ci.org/fastdlabs/http.svg?branch=master)](https://travis-ci.org/fastdlabs/http)
![Support Swoole](https://img.shields.io/badge/support-swoole-brightgreen.svg)
![Support PSR-7](https://img.shields.io/badge/support-psr7-brightgreen.svg)
![Support PSR-17](https://img.shields.io/badge/support-psr17-brightgreen.svg)
![Support PSR-18](https://img.shields.io/badge/support-psr18-brightgreen.svg)
[![Latest Stable Version](https://poser.pugx.org/fastd/http/v/stable)](https://packagist.org/packages/fastd/http) 
[![Total Downloads](https://poser.pugx.org/fastd/http/downloads)](https://packagist.org/packages/fastd/http) 
[![License](https://poser.pugx.org/fastd/http/license)](https://packagist.org/packages/fastd/http)

FastD HTTP 是一个功能完善的 HTTP 消息组件，完全实现 PSR-7、PSR-17 和 PSR-18 标准。提供服务端请求解析、客户端 HTTP 请求、响应处理等功能，并完美支持 Swoole 扩展。

## ✨ 特性

- 🎯 **PSR 标准兼容**: 完全遵循 PSR-7 (HTTP Message)、PSR-17 (HTTP Factories)、PSR-18 (HTTP Client)
- ⚡ **高性能**: 支持 Swoole 协程，提供卓越的性能表现
- 🔧 **功能完善**: 封装 Cookie、上传文件、Stream 等常用功能
- 🌐 **HTTP 客户端**: 内置 PSR-18 兼容的 HTTP 客户端，支持 cURL
- 📦 **工厂模式**: 提供 PSR-17 工厂，方便创建 HTTP 对象
- 🎭 **响应类型**: 内置 JSON、Text、Redirect 等常用响应类型

## 📋 环境要求

- **PHP**: >= 8.2
- **扩展**: 
  - ext-curl (HTTP 客户端)
  - ext-json (JSON 处理)
  - ext-zlib (压缩支持)
- **可选**: Swoole >= 4.5 (用于高性能服务器模式)

## 📦 安装

```bash
composer require fastd/http
```

## 🚀 快速开始

### 1. PSR-17 工厂 - 创建 HTTP 对象

FastD HTTP 提供了统一的工厂类，实现了所有 PSR-17 接口：

```php
<?php

use FastD\Http\Factory;

$factory = new Factory();

// 创建请求
$request = $factory->createRequest('GET', 'https://api.example.com/users');

// 创建服务器请求
$serverRequest = $factory->createServerRequest('POST', '/api/users', $_SERVER);

// 创建响应
$response = $factory->createResponse(200, 'OK');

// 创建 URI
$uri = $factory->createUri('https://api.example.com');

// 创建 Stream
$stream = $factory->createStream('Hello World');
```

### 2. 服务端请求处理

#### 从全局变量创建请求

```php
<?php

use FastD\Http\Request\ServerRequest;

// 从 PHP 全局变量创建请求
$request = ServerRequest::createServerRequestFromGlobals();

// 获取请求信息
$method = $request->getMethod();           // GET, POST, etc.
$uri = $request->getUri();                 // URI 对象
$path = $uri->getPath();                   // /api/users
$query = $uri->getQuery();                 // name=John&age=30

// 获取请求头
$headers = $request->getHeaders();
$contentType = $request->getHeaderLine('Content-Type');

// 获取请求体
$body = $request->getBody();
$parsedBody = $request->getParsedBody();   // POST 数据

// 获取上传文件
$files = $request->getUploadedFiles();
if (isset($files['avatar'])) {
    $avatar = $files['avatar'];
    echo "文件名: " . $avatar->getClientFilename();
    echo "大小: " . $avatar->getSize();
    echo "类型: " . $avatar->getClientMediaType();
    
    // 移动文件
    $avatar->moveTo('/path/to/uploads/' . $avatar->getClientFilename());
}
```

#### Swoole HTTP 服务器集成

```php
<?php

use FastD\Http\Request\SwooleServerRequest;
use FastD\Http\Response\Json;

// 创建 Swoole HTTP 服务器
$http = new Swoole\Http\Server("127.0.0.1", 9501);

$http->on('request', function ($swooleRequest, $swooleResponse) {
    // 将 Swoole 请求转换为 PSR-7 ServerRequest
    $request = SwooleServerRequest::createServerRequestFromSwoole($swooleRequest);
    
    // 处理请求
    $path = $request->getUri()->getPath();
    $method = $request->getMethod();
    
    // 返回 JSON 响应
    $response = new Json(200, [
        'path' => $path,
        'method' => $method,
        'message' => 'Hello from FastD HTTP + Swoole!'
    ]);
    
    // 发送响应
    $swooleResponse->status($response->getStatusCode());
    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            $swooleResponse->header($name, $value);
        }
    }
    $swooleResponse->end((string)$response->getBody());
});

$http->start();
```

### 3. HTTP 客户端 - PSR-18 实现

FastD HTTP 内置了 PSR-18 兼容的 HTTP 客户端：

#### 基本 GET 请求

```php
<?php

use FastD\Http\Request\Client;
use FastD\Http\Request\Request;

$client = new Client();

// 创建 GET 请求
$request = new Request('GET', 'https://api.github.com/users/octocat');

// 发送请求
$response = $client->sendRequest($request);

// 处理响应
echo "状态码: " . $response->getStatusCode() . "\n";
echo "响应头: " . print_r($response->getHeaders(), true);
echo "响应体: " . $response->getBody();
```

#### POST 请求

```php
<?php

use FastD\Http\Request\Client;
use FastD\Http\Request\Request;
use FastD\Http\Stream\Stream;

$client = new Client();

// 创建 POST 请求
$request = new Request('POST', 'https://api.example.com/users');

// 设置请求体
$request = $request->withBody(new Stream(json_encode([
    'name' => 'John Doe',
    'email' => 'john@example.com'
])));

// 设置 Content-Type
$request = $request->withHeader('Content-Type', 'application/json');

// 发送请求
$response = $client->sendRequest($request);

$data = json_decode($response->getBody(), true);
print_r($data);
```

#### 带 Cookie 的请求

```php
<?php

use FastD\Http\Request\Client;
use FastD\Http\Request\Request;
use FastD\Http\Cookie;

$client = new Client();

// 添加 Cookie
$cookie = new Cookie('session_id', 'abc123', time() + 3600);
$client = $client->withCookie($cookie);

// 发送请求
$request = new Request('GET', 'https://api.example.com/profile');
$response = $client->sendRequest($request);
```

#### 自定义 cURL 选项

```php
<?php

use FastD\Http\Request\Client;
use FastD\Http\Request\Request;

$client = new Client();

// 设置超时时间
$client = $client->withOption(CURLOPT_TIMEOUT, 10);

// 设置代理
$client = $client->withOption(CURLOPT_PROXY, 'http://proxy.example.com:8080');

// 禁用 SSL 验证
$client = $client->withOption(CURLOPT_SSL_VERIFYPEER, false);

// 发送请求
$request = new Request('GET', 'https://api.example.com/data');
$response = $client->sendRequest($request);
```

### 4. 响应类型

#### JSON 响应

```php
<?php

use FastD\Http\Response\Json;
use FastD\Http\Response\StatusCode;

// 基本 JSON 响应
$response = new Json(StatusCode::HTTP_OK, [
    'status' => 'success',
    'data' => [
        'id' => 1,
        'name' => 'John Doe'
    ]
]);

// 像数组一样访问 JSON 数据
$response['message'] = 'User created successfully';
echo $response['status']; // success

// 获取 JSON 字符串
echo (string)$response->getBody();
```

#### Text 响应

```php
<?php

use FastD\Http\Response\Text;

$response = new Text(200, 'Hello World');
$response = $response->withHeader('X-Custom-Header', 'value');

echo (string)$response->getBody(); // Hello World
```

#### Redirect 响应

```php
<?php

use FastD\Http\Response\Redirect;

// 302 临时重定向
$response = new Redirect('https://example.com/new-location');

// 301 永久重定向
$response = new Redirect('https://example.com/permanent', 301);
```

### 5. URI 处理

```php
<?php

use FastD\Http\Uri;

$uri = new Uri('https://user:pass@example.com:8080/path?query=value#fragment');

echo $uri->getScheme();   // https
echo $uri->getHost();     // example.com
echo $uri->getPort();     // 8080
echo $uri->getPath();     // /path
echo $uri->getQuery();    // query=value
echo $uri->getFragment(); // fragment

// 不可变性 - 返回新实例
$newUri = $uri->withPath('/new-path')
               ->withQuery('name=John');
```

### 6. Cookie 处理

```php
<?php

use FastD\Http\Cookie;

// 创建 Cookie
$cookie = new Cookie(
    'session',           // 名称
    'abc123',           // 值
    time() + 3600,      // 过期时间
    '/',                // 路径
    'example.com',      // 域
    true,               // Secure
    true                // HttpOnly
);

// 转换为字符串（用于 Set-Cookie 头）
echo (string)$cookie;

// 链式调用
$cookie = (new Cookie('theme', 'dark'))
    ->withPath('/admin')
    ->withDomain('example.com')
    ->withSecure(true);
```

### 7. Stream 处理

```php
<?php

use FastD\Http\Stream\Stream;

// 从字符串创建
$stream = Stream::create('Hello World');

// 从文件创建
$stream = new Stream('/path/to/file.txt', 'r');

// 读取内容
$content = $stream->getContents();

// 写入内容（需要可写模式）
$stream = new Stream('/path/to/output.txt', 'w');
$stream->write('Hello World');

// 获取大小
$size = $stream->getSize();
```

## 📚 API 参考

### 请求类

- `FastD\Http\Request\Request` - 基本 HTTP 请求 (PSR-7)
- `FastD\Http\Request\ServerRequest` - 服务器端请求 (PSR-7)
- `FastD\Http\Request\SwooleServerRequest` - Swoole 请求适配器
- `FastD\Http\Request\Client` - HTTP 客户端 (PSR-18)

### 响应类

- `FastD\Http\Response\Text` - 文本响应
- `FastD\Http\Response\Json` - JSON 响应
- `FastD\Http\Response\Redirect` - 重定向响应
- `FastD\Http\Response\StatusCode` - HTTP 状态码常量

### 其他类

- `FastD\Http\Factory` - PSR-17 工厂
- `FastD\Http\Uri` - URI 处理
- `FastD\Http\Cookie` - Cookie 处理
- `FastD\Http\Stream\Stream` - Stream 实现
- `FastD\Http\Request\UploadedFile` - 上传文件处理

## 🧪 测试

```bash
composer install
vendor/bin/phpunit
```

## 🤝 贡献

欢迎贡献代码、报告问题或提出建议！

- 🐛 [报告问题](https://github.com/fastdlabs/http/issues)
- 💡 提交功能建议
- 🔧 贡献代码和文档
- ⭐ Star 项目支持

## 📄 License

MIT License
