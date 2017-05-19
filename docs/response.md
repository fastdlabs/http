## Response

标准的 HTTP Response 响应对象，实现 PSR7，可以灵活使用 PSR7 方式进行操作。

### 新建对象

```php
<?php

$response = new \FastD\Http\Response();
$response->withContent('hello world');
```

Response 实例化接受三个参数 `statusCode, headers, version`，分别代表状态码，响应头，响应协议。

##### 输出

```php
<?php

$response = new \FastD\Http\Response();
$response->withContent('hello world');
$response->send();
```

send 方法输出响应头 + 响应体，由方法 `sendHeaders` 与 `sendBody` 组成。

##### 设置响应头

```php
<?php

$response = new \FastD\Http\Response();
$response->withContent('hello world');
$response->withExpires(new DateTime('2018-01-01'));
$response->send();
```

##### JSON

```php
<?php

$response = new \FastD\Http\JsonResponse();
$response->withContent(['foo' => 'bar']);
$response->withExpires(new DateTime('2018-01-01'));
$response->send();
```

下一节: [Stream](stream.md)
