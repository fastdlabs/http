# ServerRequest

处理由浏览器(客户端)发起的 HTTP 请求信息, 分别解析 $_SERVER, $_COOKIE, $_FILES, $_POST, $_GET 等信息。

由一系列的 Bag 对象进行封装。

通过 `FastD\Http\ServerRequest` 中的 `cookie`, `query`, `body`, `server`, `files` 进行获取。