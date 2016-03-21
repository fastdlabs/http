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
$request = Request::createRequestHandle();
```

## License MIT
