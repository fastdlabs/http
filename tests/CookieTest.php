<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Cookie;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Cookie 类测试用例
 */
class CookieTest extends TestCase
{
    // ===== 构造函数测试 =====

    public function testConstructorCreatesCookie(): void
    {
        $cookie = new Cookie('test_name', 'test_value', 3600, '/path', '.example.com', true, true);

        $this->assertSame('test_name', $cookie->getName());
        $this->assertSame('test_value', $cookie->getValue());
        $this->assertSame(3600, $cookie->getExpire());
        $this->assertSame('/path', $cookie->getPath());
        $this->assertSame('.example.com', $cookie->getDomain());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function testConstructorWithDefaultValues(): void
    {
        $cookie = new Cookie('test_name');

        $this->assertSame('test_name', $cookie->getName());
        $this->assertSame('', $cookie->getValue());
        $this->assertSame(-1, $cookie->getExpire());
        $this->assertSame('/', $cookie->getPath());
        $this->assertSame('', $cookie->getDomain());
        $this->assertFalse($cookie->isSecure());
        $this->assertFalse($cookie->isHttpOnly());
    }

    public function testConstructorThrowsExceptionForInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The cookie name "test=name" contains invalid characters.');

        new Cookie('test=name');
    }

    // ===== 静态 create 方法测试 =====

    public function testCreateMethod(): void
    {
        $cookie = Cookie::create('session', 'abc123', 3600, '/admin', '.example.com', true, true);

        $this->assertSame('session', $cookie->getName());
        $this->assertSame('abc123', $cookie->getValue());
        $this->assertSame(3600, $cookie->getExpire());
        $this->assertSame('/admin', $cookie->getPath());
        $this->assertSame('.example.com', $cookie->getDomain());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
    }

    // ===== 获取方法测试 =====

    public function testGetName(): void
    {
        $cookie = new Cookie('test_name');
        $this->assertSame('test_name', $cookie->getName());
    }

    public function testGetValue(): void
    {
        $cookie = new Cookie('name', 'test_value');
        $this->assertSame('test_value', $cookie->getValue());
    }

    public function testGetExpire(): void
    {
        $cookie = new Cookie('name', '', 3600);
        $this->assertSame(3600, $cookie->getExpire());
    }

    public function testGetPath(): void
    {
        $cookie = new Cookie('name', '', -1, '/test');
        $this->assertSame('/test', $cookie->getPath());
    }

    public function testGetDomain(): void
    {
        $cookie = new Cookie('name', '', -1, '/', '.example.com');
        $this->assertSame('.example.com', $cookie->getDomain());
    }

    public function testIsSecure(): void
    {
        $cookie = new Cookie('name', '', -1, '/', '', true);
        $this->assertTrue($cookie->isSecure());
    }

    public function testIsHttpOnly(): void
    {
        $cookie = new Cookie('name', '', -1, '/', '', false, true);
        $this->assertTrue($cookie->isHttpOnly());
    }

    // ===== 链式设置方法测试 =====

    public function testWithName(): void
    {
        $original = new Cookie('old_name');
        $modified = $original->withName('new_name');

        $this->assertSame('old_name', $original->getName()); // 原对象不变
        $this->assertSame('new_name', $modified->getName()); // 新对象改变
        $this->assertNotSame($original, $modified); // 不是同一个实例
    }

    public function testWithNameValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The cookie name "bad=name" contains invalid characters.');

        $cookie = new Cookie('good_name');
        $cookie->withName('bad=name');
    }

    public function testWithValue(): void
    {
        $original = new Cookie('name', 'old_value');
        $modified = $original->withValue('new_value');

        $this->assertSame('old_value', $original->getValue());
        $this->assertSame('new_value', $modified->getValue());
    }

    public function testWithExpire(): void
    {
        $original = new Cookie('name', '', 1000);
        $modified = $original->withExpire(2000);

        $this->assertSame(1000, $original->getExpire());
        $this->assertSame(2000, $modified->getExpire());
    }

    public function testWithPath(): void
    {
        $original = new Cookie('name', '', -1, '/old');
        $modified = $original->withPath('/new');

        $this->assertSame('/old', $original->getPath());
        $this->assertSame('/new', $modified->getPath());
    }

    public function testWithDomain(): void
    {
        $original = new Cookie('name', '', -1, '/', 'old.com');
        $modified = $original->withDomain('new.com');

        $this->assertSame('old.com', $original->getDomain());
        $this->assertSame('new.com', $modified->getDomain());
    }

    public function testWithSecure(): void
    {
        $original = new Cookie('name', '', -1, '/', '', false);
        $modified = $original->withSecure(true);

        $this->assertFalse($original->isSecure());
        $this->assertTrue($modified->isSecure());
    }

    public function testWithHttpOnly(): void
    {
        $original = new Cookie('name', '', -1, '/', '', false, false);
        $modified = $original->withHttpOnly(true);

        $this->assertFalse($original->isHttpOnly());
        $this->assertTrue($modified->isHttpOnly());
    }

    // ===== 链式调用测试 =====

    public function testChainedWithCalls(): void
    {
        $cookie = (new Cookie('session'))
            ->withValue('abc123')
            ->withExpire(3600)
            ->withPath('/admin')
            ->withDomain('.example.com')
            ->withSecure(true)
            ->withHttpOnly(true);

        $this->assertSame('session', $cookie->getName());
        $this->assertSame('abc123', $cookie->getValue());
        $this->assertSame(3600, $cookie->getExpire());
        $this->assertSame('/admin', $cookie->getPath());
        $this->assertSame('.example.com', $cookie->getDomain());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function testChainedCallsPreservesOriginal(): void
    {
        $original = new Cookie('name', 'old_value', 1000, '/old', 'old.com', false, false);

        $modified = $original
            ->withValue('new_value')
            ->withExpire(2000)
            ->withPath('/new')
            ->withDomain('new.com')
            ->withSecure(true)
            ->withHttpOnly(true);

        // 原对象保持不变
        $this->assertSame('old_value', $original->getValue());
        $this->assertSame(1000, $original->getExpire());
        $this->assertSame('/old', $original->getPath());
        $this->assertSame('old.com', $original->getDomain());
        $this->assertFalse($original->isSecure());
        $this->assertFalse($original->isHttpOnly());

        // 新对象包含修改
        $this->assertSame('new_value', $modified->getValue());
        $this->assertSame(2000, $modified->getExpire());
        $this->assertSame('/new', $modified->getPath());
        $this->assertSame('new.com', $modified->getDomain());
        $this->assertTrue($modified->isSecure());
        $this->assertTrue($modified->isHttpOnly());
    }

    // ===== __toString 方法测试 =====

    public function testToStringBasic(): void
    {
        $cookie = new Cookie('session', 'abc123', -1, '');
        $expected = 'session=abc123';

        $this->assertSame($expected, (string)$cookie);
    }

    public function testToStringWithAllAttributes(): void
    {
        $cookie = new Cookie('session', 'abc123', 3600, '/path', '.example.com', true, true);
        $result = (string)$cookie;

        $this->assertStringContainsString('session=abc123', $result);
        $this->assertStringContainsString('expires=', $result);
        $this->assertStringContainsString('path=/path', $result);
        $this->assertStringContainsString('domain=.example.com', $result);
        $this->assertStringContainsString('secure', $result);
        $this->assertStringContainsString('httponly', $result);
    }

    public function testToStringWithEmptyValueButPositiveExpire(): void
    {
        $cookie = new Cookie('session', '', 3600);
        $result = (string)$cookie;
        $this->assertStringContainsString('session=', $result);
        $this->assertStringContainsString('expires=', $result);
        $this->assertStringContainsString('deleted', $result);
    }

    public function testToStringWithEmptyValueAndNegativeExpire(): void
    {
        $cookie = new Cookie('session', '', -1);
        $result = (string)$cookie;

        $this->assertStringContainsString('session=deleted', $result);
        $this->assertStringContainsString('expires=', $result);
    }

    public function testToStringWithSpecialCharacters(): void
    {
        $cookie = new Cookie('user_name', 'John+Doe@domain.com');
        $result = (string)$cookie;

        $this->assertStringContainsString('user_name=John%2BDoe%40domain.com', $result);
    }

    public function testToStringWithDefaultPathNotAdded(): void
    {
        $cookie = new Cookie('name', 'value', -1, '');
        $result = (string)$cookie;

        // 默认路径 / 不应该被添加到字符串中
        $this->assertStringNotContainsString('path=/', $result);
    }

    public function testToStringWithPathAdded(): void
    {
        $cookie = new Cookie('name', 'value', -1, '/admin');
        $result = (string)$cookie;

        $this->assertStringContainsString('path=/admin', $result);
    }

    // ===== 边界条件测试 =====

    public function testWithZeroExpire(): void
    {
        $cookie = new Cookie('name', 'value', 0);
        $result = (string)$cookie;

        // 0 expire 应该不包含 expires 部分
        $this->assertStringNotContainsString('expires=', $result);
    }

    public function testWithNegativeExpire(): void
    {
        $cookie = new Cookie('name', 'value', -1);
        $result = (string)$cookie;

        // -1 expire 应该不包含 expires 部分（除非值为空）
        $this->assertStringNotContainsString('expires=', $result);
    }

    public function testWithEmptyPathAndDomain(): void
    {
        $cookie = new Cookie('name', 'value', -1, '', '');
        $result = (string)$cookie;

        // 空字符串应该不包含 path= 和 domain=
        $this->assertStringNotContainsString('path=', $result);
        $this->assertStringNotContainsString('domain=', $result);
    }

    public function testWithDefaultPathNotAdded(): void
    {
        $cookie = new Cookie('name', 'value', -1, '/');
        $result = (string)$cookie;

        // 默认路径 / 不应该被添加
        $this->assertStringContainsString('path=/', $result);
    }

    public function testWithFalseSecureAndHttpOnly(): void
    {
        $cookie = new Cookie('name', 'value', -1, '/', '', false, false);
        $result = (string)$cookie;

        // false 值不应该包含 secure 和 httponly
        $this->assertStringNotContainsString('secure', $result);
        $this->assertStringNotContainsString('httponly', $result);
    }

    // ===== 性能测试（可选） =====

    public function testPerformanceWithMultipleChains(): void
    {
        $start = microtime(true);

        $cookie = new Cookie('test');
        for ($i = 0; $i < 100; $i++) {
            $cookie = $cookie->withValue("value_$i")
                ->withExpire($i)
                ->withPath("/path_$i");
        }

        $end = microtime(true);
        $duration = ($end - $start) * 1000; // 转换为毫秒

        // 应该在合理时间内完成（例如 100ms 内）
        $this->assertLessThan(100, $duration, 'Chained operations should be fast');
    }

    public function testImmutabilityPerformance(): void
    {
        $start = microtime(true);

        $cookies = [];
        for ($i = 0; $i < 1000; $i++) {
            $cookies[] = (new Cookie("name_$i", "value_$i"))->withExpire($i);
        }

        $end = microtime(true);
        $duration = ($end - $start) * 1000;

        $this->assertLessThan(100, $duration, 'Multiple clone operations should be efficient');
    }
}