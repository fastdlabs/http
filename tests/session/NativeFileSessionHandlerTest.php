<?php

declare(strict_types=1);

namespace FastD\Http\Tests;

use FastD\Http\Session\Handler\NativeFileSessionHandler;
use PHPUnit\Framework\TestCase;

/**
 * NativeFileSessionHandler 单元测试：真实文件系统读写、过期与路径安全性。
 */
class NativeFileSessionHandlerTest extends TestCase
{
    private string $dir;

    private NativeFileSessionHandler $handler;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/fastd-session-' . bin2hex(random_bytes(6));
        $this->handler = new NativeFileSessionHandler($this->dir, 60);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->dir);
    }

    public function testWriteReadRoundtrip(): void
    {
        $data = ['user' => 'jan', 'nested' => ['a' => 1, 'b' => '中文']];

        $this->assertTrue($this->handler->write('abc123', $data));
        $this->assertTrue($this->handler->exists('abc123'));
        $this->assertSame($data, $this->handler->read('abc123'));
    }

    public function testReadReturnsEmptyArrayForUnknownSession(): void
    {
        $this->assertSame([], $this->handler->read('missing'));
        $this->assertFalse($this->handler->exists('missing'));
    }

    public function testDestroyRemovesTheSession(): void
    {
        $this->handler->write('abc123', ['user' => 'jan']);

        $this->assertTrue($this->handler->destroy('abc123'));
        $this->assertFalse($this->handler->exists('abc123'));
        $this->assertFalse($this->handler->destroy('abc123'));
    }

    public function testExpiredSessionIsNotReturnedAndFileIsRemoved(): void
    {
        $handler = new NativeFileSessionHandler($this->dir, 0);
        $handler->write('expired', ['user' => 'jan']);

        $this->assertFalse($handler->exists('expired'));
        $this->assertSame([], $handler->read('expired'));
        $this->assertSame([], glob($this->dir . '/fastd_session_*') ?: []);
    }

    public function testMalformedPayloadDegradesToEmptyArray(): void
    {
        $this->handler->write('abc123', ['user' => 'jan']);

        $files = glob($this->dir . '/*') ?: [];
        $this->assertCount(1, $files);
        file_put_contents($files[0], 'not-json{');

        $this->assertSame([], $this->handler->read('abc123'));
    }

    public function testUntrustedSessionIdNeverEscapesTheSavePath(): void
    {
        $this->assertTrue($this->handler->write('../evil', ['user' => 'jan']));

        $files = glob($this->dir . '/*') ?: [];
        $this->assertCount(1, $files);
        $this->assertStringNotContainsString('..', basename($files[0]));
        $this->assertFileDoesNotExist(dirname($this->dir) . '/evil');
        $this->assertSame(['user' => 'jan'], $this->handler->read('../evil'));
    }

    public function testWriteReturnsFalseWhenSavePathIsNotWritable(): void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('Permission checks are not enforced for the root user.');
        }

        chmod($this->dir, 0500);

        try {
            $this->assertFalse($this->handler->write('abc123', ['user' => 'jan']));
            $this->assertFalse($this->handler->exists('abc123'));
        } finally {
            chmod($this->dir, 0700);
        }
    }

    public function testMissingSavePathIsCreatedOnDemand(): void
    {
        $handler = new NativeFileSessionHandler($this->dir . '/nested/deep', 60);

        $this->assertDirectoryExists($this->dir . '/nested/deep');
        $this->assertTrue($handler->write('abc123', ['user' => 'jan']));
        $this->assertSame(['user' => 'jan'], $handler->read('abc123'));
    }

    public function testGcRemovesOnlyExpiredSessions(): void
    {
        $handler = new NativeFileSessionHandler($this->dir, 60);
        $handler->write('fresh', ['user' => 'jan']);
        $handler->write('stale', ['user' => 'jan']);

        // Age both files beyond the TTL, then let gc sweep them.
        foreach (glob($this->dir . '/fastd_session_*') ?: [] as $file) {
            touch($file, time() - 120);
        }

        $this->assertSame(2, $handler->gc());
        $this->assertSame([], glob($this->dir . '/fastd_session_*') ?: []);
    }

    private function removeDirectory(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        @rmdir($dir);
    }
}
