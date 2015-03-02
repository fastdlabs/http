#Dobee php simple framework HTTP Component.

##1.创建Http Request 对象

```
$request = new \Dobee\Http\Request
// 或者
$request = \Dobee\Http\Request::createGlobalRequest();
// 更加建议使用后者创建request对象
```

##2.获取Request对象内置资源
###2.1获取Request GET数据
```
$get = $request->getQuery();  // $request->query;
```

####2.1.1获取GET数据

**例如：**

```
http://path/to/demo.php?name=janhuang&age=23
```

```
// get name value
$get->get('name'); // $request->getQuery()->get('name');
// get age value
$get->get('age'); // $request->getQuery()->get('age');
```

###2.2获取Request POST数据

```
$post = $request->getRequest();
```

**例如：**

```
form data:
{
    "name": "janhuang",
    "age": 23
}
```

```
//get name value
$post->get('name'); // $request->getRequest()->get('name');
// get age value
$post->get('age'); // $request->getRequest()->get('age');
```

###2.3获取Request FILES数据

```
$files = $request->getFiles();
```

####2.3.1获取制定Files信息

**例如有一个多文件：**
```
array(
    'file1' => // something,
    'file2' => // somthing
)
```

```
$file = $files->getFile('file1');
```
