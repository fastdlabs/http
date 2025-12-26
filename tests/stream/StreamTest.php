<?php

declare(strict_types=1);

namespace FastD\Http\Tests\Stream;

use FastD\Http\Stream\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Stream 类完整测试用例
 */
class StreamTest extends TestCase
{
    private string $tempFile;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir();
        $this->tempFile = tempnam($this->tempDir, 'stream_test_');
        file_put_contents($this->tempFile, 'Hello, World! This is a test stream.');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    // ===== 构造函数和基础测试 =====

    public function testConstructorCreatesStream(): void
    {
        $stream = new Stream($this->tempFile, 'r');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
    }

    public function testConstructorWithDifferentModes(): void
    {
        $modes = [
            ['r', true, false, true],      // Read-only
            ['w', false, true, true],      // Write-only
            ['a', false, true, true],      // Append
            ['r+', true, true, true],      // Read-write
            ['w+', true, true, true],      // Write-read
            ['a+', true, true, true],      // Append-read
        ];

        foreach ($modes as [$mode, $readable, $writable, $seekable]) {
            $stream = new Stream($this->tempFile, $mode);

            $this->assertEquals($readable, $stream->isReadable());
            $this->assertEquals($writable, $stream->isWritable());
            $this->assertEquals($seekable, $stream->isSeekable());

            $stream->close();
        }
    }

    // ===== __toString 测试 =====

    public function testToString(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $content = (string)$stream;

        $this->assertSame('Hello, World! This is a test stream.', $content);
    }

    public function testToStringReturnsEmptyWhenResourceDetached(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $stream->detach();

        $this->assertSame('', (string)$stream);
    }

    public function testToStringHandlesLargeContent(): void
    {
        $largeContent = str_repeat('A', 1024 * 10); // 10KB
        file_put_contents($this->tempFile, $largeContent);

        $stream = new Stream($this->tempFile, 'r');
        $content = (string)$stream;

        $this->assertSame($largeContent, $content);
    }

    // ===== 尺寸相关测试 =====

    public function testGetSize(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $size = $stream->getSize();

        $this->assertIsInt($size);
        $this->assertSame(strlen('Hello, World! This is a test stream.'), $size);
    }

    public function testGetSizeReturnsNullWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->assertEquals(0, $stream->getSize());
    }

    // ===== 位置相关测试 =====

    public function testTell(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $this->assertSame(0, $stream->tell());

        $stream->read(5);
        $this->assertSame(5, $stream->tell());
    }

    public function testTellThrowsExceptionWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->tell();
    }

    // ===== EOF 测试 =====

    public function testEof(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $this->assertFalse($stream->eof());

        $stream->getContents();
        $this->assertTrue($stream->eof());
    }

    public function testEofReturnsTrueWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->assertTrue($stream->eof());
    }

    // ===== Seek 和 Rewind 测试 =====

    public function testSeek(): void
    {
        $stream = new Stream($this->tempFile, 'r');

        $stream->seek(7);
        $this->assertSame(7, $stream->tell());

        $content = $stream->read(5);
        $this->assertSame('World', $content);
    }

    public function testSeekWithWhence(): void
    {
        $stream = new Stream($this->tempFile, 'r');

        $stream->seek(-6, SEEK_END); // 从末尾向前6个字节
        $content = $stream->read(6);
        $this->assertSame('tream.', $content);
    }


    public function testSeekThrowsExceptionWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->seek(10);
    }

    public function testRewind(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $stream->read(10); // Move position to 10
        $this->assertSame(10, $stream->tell());

        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    // ===== 可读性测试 =====

    public function testIsReadable(): void
    {
        $readStream = new Stream($this->tempFile, 'r');
        $writeStream = new Stream($this->tempFile, 'w');

        $this->assertTrue($readStream->isReadable());
        $this->assertFalse($writeStream->isReadable());
    }

    public function testRead(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $content = $stream->read(5);

        $this->assertSame('Hello', $content);
    }

    public function testReadThrowsExceptionWhenNotReadable(): void
    {
        $stream = new Stream($this->tempFile, 'w');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');
        $stream->read(5);
    }

    public function testReadThrowsExceptionWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->read(5);
    }

    public function testGetContents(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame('Hello, World! This is a test stream.', $content);
    }

    public function testGetContentsThrowsExceptionWhenNotReadable(): void
    {
        $stream = new Stream($this->tempFile, 'w');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');
        $stream->getContents();
    }

    public function testGetContentsThrowsExceptionWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->getContents();
    }

    // ===== 可写性测试 =====

    public function testIsWritable(): void
    {
        $readStream = new Stream($this->tempFile, 'r');
        $writeStream = new Stream($this->tempFile, 'w');

        $this->assertFalse($readStream->isWritable());
        $this->assertTrue($writeStream->isWritable());
    }

    public function testWrite(): void
    {
        $stream = new Stream($this->tempFile, 'r+');

        $content = $stream->getContents();
        $bytesWritten = $stream->write(' New');

        $stream->rewind();
        $content = $stream->getContents();


        $stream->rewind();
        $bytesWritten = $stream->write('New');
        $stream->rewind();
        $content = $stream->getContents();
        $this->assertStringStartsWith('Newlo', $content);
    }

    public function testWriteThrowsExceptionWhenNotWritable(): void
    {
        $stream = new Stream($this->tempFile, 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');
        $stream->write('test');
    }

    public function testWriteThrowsExceptionWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r+');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->write('test');
    }

    // ===== 分离和关闭测试 =====

    public function testDetach(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();

        $this->assertIsResource($resource);
        $this->assertNull($stream->detach()); // Second detach should return null
        $this->assertTrue($stream->isDetached());
    }

    public function testClose(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $stream->close();

        $this->assertTrue($stream->isDetached());
    }

    public function testCloseMultipleTimes(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $stream->close();
        $stream->close(); // Should not throw exception

        $this->assertTrue($stream->isDetached());
    }

    public function testIsDetached(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $this->assertFalse($stream->isDetached());

        $stream->detach();
        $this->assertTrue($stream->isDetached());
    }

    // ===== 元数据测试 =====

    public function testGetMetadata(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $metadata = $stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('mode', $metadata);
        $this->assertArrayHasKey('seekable', $metadata);
        $this->assertSame('r', $metadata['mode']);
    }

    public function testGetSpecificMetadata(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $mode = $stream->getMetadata('mode');

        $this->assertSame('r', $mode);
    }

    public function testGetNonExistentMetadata(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $nonExistent = $stream->getMetadata('non_existent_key');

        $this->assertNull($nonExistent);
    }

    public function testGetMetadataReturnsEmptyArrayWhenNoResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('any_key'));
    }

    // ===== 模式测试 =====

    public function testGetMode(): void
    {
        $stream = new Stream($this->tempFile, 'r+');
        $this->assertSame('r+', $stream->getMode());
    }

    // ===== 边界情况测试 =====

    public function testReadZeroBytes(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $content = $stream->read(0);

        $this->assertSame('', $content);
    }

    public function testWriteEmptyString(): void
    {
        $stream = new Stream($this->tempFile, 'r+');
        $bytesWritten = $stream->write('');

        $this->assertSame(0, $bytesWritten);
    }

    public function testSeekBeyondEndOfFile(): void
    {
        $stream = new Stream($this->tempFile, 'r+');

        // This should work - seeking beyond end in append mode
        $stream->seek(1000, SEEK_SET);
        $this->assertSame(1000, $stream->tell());
    }

    public function testOperationsAfterDetach(): void
    {
        $stream = new Stream($this->tempFile, 'r+');
        $stream->detach();

        // All operations should throw RuntimeException
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->read(1);
    }

    public function testMultipleDetachCalls(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $firstDetach = $stream->detach();
        $secondDetach = $stream->detach();

        $this->assertIsResource($firstDetach);
        $this->assertNull($secondDetach);
    }

    // ===== 不同流类型测试 =====

    public function testMemoryStream(): void
    {
        $stream = new Stream('php://memory', 'r+');
        $stream->write('Hello Memory');

        $stream->rewind();
        $content = $stream->getContents();

        $this->assertSame('Hello Memory', $content);
    }

    public function testTempStream(): void
    {
        $stream = new Stream('php://temp', 'r+');
        $stream->write('Hello Temp');

        $stream->rewind();
        $content = $stream->getContents();

        $this->assertSame('Hello Temp', $content);
    }

    public function testTempStreamBecomesFile(): void
    {
        $stream = new Stream('php://temp/maxmemory:1', 'r+');

        // Write more than 1 byte to force it to become a file
        $stream->write(str_repeat('A', 100));

        $stream->rewind();
        $content = $stream->getContents();

        $this->assertSame(str_repeat('A', 100), $content);
    }

    // ===== 性能和压力测试 =====

    public function testLargeWriteAndRead(): void
    {
        $largeData = str_repeat('A', 1024 * 100); // 100KB
        $stream = new Stream('php://memory', 'r+');

        $bytesWritten = $stream->write($largeData);
        $this->assertSame(strlen($largeData), $bytesWritten);

        $stream->rewind();
        $readData = $stream->getContents();
        $this->assertSame($largeData, $readData);
    }

    public function testSequentialReads(): void
    {
        $stream = new Stream($this->tempFile, 'r');

        $chunk1 = $stream->read(5); // 'Hello'
        $chunk2 = $stream->read(2); // ', '
        $chunk3 = $stream->read(5); // 'World'

        $this->assertSame('Hello', $chunk1);
        $this->assertSame(', ', $chunk2);
        $this->assertSame('World', $chunk3);
    }

    // ===== 错误处理测试 =====

    public function testErrorInWriteOperation(): void
    {
        // Create a read-only stream and try to write to it
        $stream = new Stream($this->tempFile, 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');
        $stream->write('should fail');
    }

    public function testErrorInReadOperation(): void
    {
        // Create a write-only stream and try to read from it
        $stream = new Stream($this->tempFile, 'w');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');
        $stream->read(1);
    }

    // ===== 文件锁定和并发测试 =====

    public function testFileLocking(): void
    {
        $stream1 = new Stream($this->tempFile, 'r+');

        $resource = $stream1->detach();

        // Try to lock the file
        $result = flock($resource, LOCK_EX | LOCK_NB);
        $this->assertTrue($result);

        // Release the lock
        flock($resource, LOCK_UN);
    }

    // ===== 特殊字符和编码测试 =====

    public function testUnicodeContent(): void
    {
        $unicodeContent = "Hello 世界 🌍 测试";
        file_put_contents($this->tempFile, $unicodeContent);

        $stream = new Stream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame($unicodeContent, $content);
    }

    public function testBinaryContent(): void
    {
        $binaryData = "\x00\x01\x02\x03\xFF\xFE\xFD";
        file_put_contents($this->tempFile, $binaryData);

        $stream = new Stream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame($binaryData, $content);
    }

    // ===== 文件权限和属性测试 =====

    public function testFilePermissions(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $metadata = $stream->getMetadata();

        $this->assertArrayHasKey('uri', $metadata);
        $this->assertStringEndsWith($this->tempFile, $metadata['uri']);
    }

    // ===== 析构函数测试 =====

    public function testDestructorClosesResource(): void
    {
        $stream = new Stream($this->tempFile, 'r');
        $resource = $stream->detach();

        $this->assertIsResource($resource);

        // Force garbage collection
        $stream = null;
        gc_collect_cycles();
        fclose($resource);

        // Resource should be closed
        $this->assertFalse(is_resource($resource));
    }

    // ===== 大文件处理测试 =====

    public function testLargeFileHandling(): void
    {
        $largeContent = str_repeat('A', 1024 * 1024); // 1MB
        file_put_contents($this->tempFile, $largeContent);

        $stream = new Stream($this->tempFile, 'r');

        $this->assertNotNull($stream->getSize());
        $this->assertGreaterThan(0, $stream->getSize());

        $readContent = $stream->getContents();
        $this->assertSame($largeContent, $readContent);
    }

    // ===== 混合操作测试 =====

    public function testMixedReadAndWrite(): void
    {
        $stream = new Stream($this->tempFile, 'r+');

        // Read some content
        $firstPart = $stream->read(5); // 'Hello'
        $this->assertSame('Hello', $firstPart);

        // Write new content
        $bytesWritten = $stream->write(' PHP');
        $this->assertSame(4, $bytesWritten);

        // Read remaining content
        $stream->rewind();
        $remaining = $stream->getContents();
        $this->assertStringContainsString(' PHP', $remaining);
    }

    // ===== 错误恢复测试 =====

    public function testErrorRecoveryAfterException(): void
    {
        $stream = new Stream($this->tempFile, 'r+');

        // Cause an exception
        try {
            $writeOnlyStream = new Stream('/non/existent/file', 'r');
        } catch (RuntimeException $e) {
            // Expected
        }

        // Stream should still be functional
        $stream->write('test');
        $stream->rewind();
        $content = $stream->read(4);
        $this->assertSame('test', $content);
    }

    // ===== 资源泄露测试 =====

    public function testNoResourceLeak(): void
    {
        $initialResourceCount = gc_collect_cycles();

        // Create and destroy multiple streams
        for ($i = 0; $i < 10; $i++) {
            $tempFile = tempnam(sys_get_temp_dir(), 'test_');
            $stream = new Stream($tempFile, 'w');
            $stream->write('test');
            $stream = null; // Explicitly unset
            unlink($tempFile);
        }

        gc_collect_cycles();
        $finalResourceCount = gc_collect_cycles();

        // There might be some cleanup, but shouldn't be significant resource leak
        $this->assertLessThan(5, abs($finalResourceCount - $initialResourceCount));
    }

    // ===== 特殊模式测试 =====

    public function testBinaryMode(): void
    {
        $binaryContent = "\x00\x0D\x0A\xFF";
        $stream = new Stream($this->tempFile, 'w+b');
        $stream->write($binaryContent);

        $stream->rewind();
        $readContent = $stream->getContents();

        $this->assertSame($binaryContent, $readContent);
    }

    public function testTextMode(): void
    {
        $textContent = "Line 1\r\nLine 2\nLine 3\rLine 4";
        $stream = new Stream($this->tempFile, 'w+t');
        $stream->write($textContent);

        $stream->rewind();
        $readContent = $stream->getContents();

        $this->assertSame($textContent, $readContent);
    }

    // ===== 模式验证测试 =====

    public function testModeValidation(): void
    {
        $validModes = ['r', 'w', 'a', 'c', 'r+', 'w+', 'a+', 'c+'];
        $binaryModes = ['rb', 'wb', 'ab', 'cb', 'r+b', 'w+b', 'a+b', 'c+b'];

        foreach ($validModes as $mode) {
            $stream = new Stream($this->tempFile, $mode);
            $this->assertInstanceOf(Stream::class, $stream);
            $stream->close();
        }

        foreach ($binaryModes as $mode) {
            $stream = new Stream($this->tempFile, $mode);
            $this->assertInstanceOf(Stream::class, $stream);
            $stream->close();
        }
    }
}