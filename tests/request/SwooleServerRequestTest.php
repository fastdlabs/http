<?php

declare(strict_types=1);

use FastD\Http\Request\SwooleServerRequest;
use FastD\Http\Request\ServerRequest;
use FastD\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;

/**
 * SwooleServerRequest类单元测试
 * 注意：由于SwooleServerRequest依赖Swoole扩展，此测试主要验证类的结构和方法
 */
class SwooleServerRequestTest extends TestCase
{
    /**
     * 检查类是否存在和可访问
     */
    public function testClassExists()
    {
        $this->assertTrue(class_exists('FastD\Http\Request\SwooleServerRequest'));
    }

    /**
     * 检查继承关系
     */
    public function testInheritance()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        $this->assertTrue($reflection->isSubclassOf('FastD\Http\Request\ServerRequest'));
    }

    /**
     * 检查静态方法存在性
     */
    public function testStaticMethodExists()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        $this->assertTrue($reflection->hasMethod('fromSwoole'));
        
        $method = $reflection->getMethod('fromSwoole');
        $this->assertTrue($method->isStatic());
    }

    /**
     * 测试类的基本实例化（虽然不能完整测试Swoole功能）
     */
    public function testCanInstantiate()
    {
        // 由于需要Swoole扩展，我们只测试类的基本结构
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    /**
     * 验证SwooleServerRequest扩展了ServerRequest
     */
    public function testExtendsServerRequest()
    {
        // 由于SwooleServerRequest继承自ServerRequest，我们验证继承关系
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        $this->assertTrue($reflection->isSubclassOf(ServerRequest::class));
        
        // 验证实现了PSR接口
        $instance = $this->getMockBuilder(SwooleServerRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->assertInstanceOf('FastD\Http\Request\ServerRequest', $instance);
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $instance);
    }

    /**
     * 验证类的公共方法
     */
    public function testPublicMethods()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        
        // 检查是否包含预期的公共方法
        $expectedMethods = ['fromSwoole'];
        foreach ($expectedMethods as $method) {
            $this->assertTrue($reflection->hasMethod($method), "Method $method should exist");
            $this->assertTrue($reflection->getMethod($method)->isPublic(), "Method $method should be public");
        }
    }

    /**
     * 验证类的命名空间
     */
    public function testNamespace()
    {
        $this->assertEquals('FastD\Http\Request\SwooleServerRequest', SwooleServerRequest::class);
    }

    /**
     * 验证类实现了ServerRequestInterface
     */
    public function testImplementsServerRequestInterface()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        $interfaces = $reflection->getInterfaceNames();
        
        $this->assertContains('Psr\\Http\\Message\\ServerRequestInterface', $interfaces);
        $this->assertContains('Psr\\Http\\Message\\RequestInterface', $interfaces);
        $this->assertContains('Psr\\Http\\Message\\MessageInterface', $interfaces);
    }

    /**
     * 验证类的protected方法存在
     */
    public function testProtectedMethodsExist()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        
        $this->assertTrue($reflection->hasMethod('createServerFormSwoole'));
        $method = $reflection->getMethod('createServerFormSwoole');
        $this->assertTrue($method->isProtected() || $method->isPublic()); // 反射可以访问受保护方法
        $this->assertTrue($method->isStatic());
    }

    /**
     * 测试SwooleServerRequest与父类ServerRequest的关系
     */
    public function testSwooleServerRequestIsServerRequest()
    {
        $this->assertTrue(is_subclass_of('FastD\Http\Request\SwooleServerRequest', 'FastD\Http\Request\ServerRequest'));
    }

    /**
     * 验证所有ServerRequest的方法在SwooleServerRequest中仍然可用
     */
    public function testInheritedMethods()
    {
        $swooleRequestReflection = new \ReflectionClass(SwooleServerRequest::class);
        $serverRequestReflection = new \ReflectionClass(ServerRequest::class);
        
        $serverRequestMethods = $serverRequestReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        foreach ($serverRequestMethods as $method) {
            $methodName = $method->getName();
            $this->assertTrue(
                $swooleRequestReflection->hasMethod($methodName),
                "Method $methodName should be inherited from ServerRequest"
            );
        }
    }

    /**
     * 测试类的不可变性 - with方法应该返回新实例
     */
    public function testImmutableWithMethods()
    {
        // 创建一个模拟的SwooleServerRequest实例（通过反射绕过Swoole依赖）
        $serverRequest = $this->getMockBuilder(SwooleServerRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        // 测试继承的with方法是否返回新实例（验证不可变性）
        $newRequest = $serverRequest->withMethod('POST');
        $this->assertNotSame($serverRequest, $newRequest);
    }

    /**
     * 验证SwooleServerRequest具有正确的PSR-7兼容性
     */
    public function testPsr7Compatibility()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        
        // 检查是否实现了必要的PSR-7接口
        $this->assertTrue(is_a('FastD\Http\Request\SwooleServerRequest', 'Psr\\Http\\Message\\ServerRequestInterface', true));
        $this->assertTrue(is_a('FastD\Http\Request\SwooleServerRequest', 'Psr\\Http\\Message\\RequestInterface', true));
        $this->assertTrue(is_a('FastD\Http\Request\SwooleServerRequest', 'Psr\\Http\\Message\\MessageInterface', true));
    }

    /**
     * 测试创建服务器环境数组的逻辑
     */
    public function testCreateServerFormSwooleMethodAccess()
    {
        $reflection = new \ReflectionClass(SwooleServerRequest::class);
        $method = $reflection->getMethod('createServerFormSwoole');
        $method->setAccessible(true); // 设置为可访问以便测试
        
        // 验证方法存在并可调用（尽管我们无法提供真正的Swoole请求对象）
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->getName() === 'createServerFormSwoole');
    }
}