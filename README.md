# FastD Http

简单的 Http 协议组件, 用于解析 Http 请求信息.

## 要求

php >= 7.0

## 安装

```
composer -vvv require "fastd/http:2.0.x-dev"
```

## 使用

每个 Http 都是一个请求，每次上来的请求都需要有 Http 组件进行处理，但一个请求只需要处理一次，因此这里的实例化可以只需一次即可。

```php
use FastD\Http\Request;

$request = Request::createRequestHandle();
```

### 上传参数处理

当用户访问 `http://examples.com/?name=jan` 链接的时候，可以通过 `query` 对象访问对应的参数。

```
$request->query->hasGet('name', null);
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

### cookie 处理

cookie 处理和我们日常中的 cookie 处理 API 是一致的，使用上也并没有太大差异。

----

## api

### 获取 $_GET 参数

```
public $query: QueryAttribute
```

### 获取 POST|PUT|DELETE 等参数

```
public $request: RequestAttribute
```

### 获取 $_SERVER 参数

```
public $server: ServerAttribute
```

### 获取 header 参数

```
public $header: HeaderAttribute
```

### 获取 $_COOKIE 参数

```
public $cookies: CookiesAttribute
```

### 获取 $_SESSION 参数

因为 session 的特殊性，所以 session 的处理和其他不一样，获取需要获取 `getSessionHandle`

```
public function getSessionHandle(\FastD\Http\Session\Storage\SessionStorageInterface $sessionStorageInterface): Session
```

### 获取 path info

path info 更多用在配合路由处理方面。

```
public function getPathInfo(): string
```

### 获取请求ip

```
public function getClientIp(): string
```

### 获取 User-Agent

```
public function getUserAgent(): string
```

### 判断ajax 请求

```
public function isXmlHttpRequest(): boolean
```

## License MIT
