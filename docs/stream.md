## Stream 流

PHP 默认提供很多的流处理, 详细可查看: [协议](http://php.net/manual/zh/wrappers.php) [Stream](http://php.net/manual/zh/wrappers.php.php)

### Stream

在 HTTP 请求和响应中，默认使用 `php://memory` wb 模式进行访问处理。实现细节实现 PSR7，具体可查看源代码进行研究。

且在 Stream (可以看成是一个文件操作，其实文件也是一个流) 中，需要传入对应的 `stream`，如: `php://temp`，内部实现使用 `fopen`，`fwrite`，`fread` 进行访问控制。

```php
<?php

$stream = new \FastD\Http\Stream('php://temp');
$stream->write('hello world');
echo $stream;
$stream->close(); 
```

##### PHPInput Stream

PHPInput Stream 用于常用于处理 PUT，DELETE 请求等数据。

```php
<?php
$stream = new \FastD\Http\PhpInputStream();
echo $stream;
print_r($stream->getParsedContents());
```

使用 `getParsedContents` 获取解析后的数据
