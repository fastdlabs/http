<?php

declare(strict_types=1);

namespace FastD\Http\Tests\Stream;

use FastD\Http\Stream\PhpInputStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * PhpInputStream 类完整单元测试
 */
class PhpInputStreamTest extends TestCase
{
    private string $tempDir;
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir();
        $this->tempFile = tempnam($this->tempDir, 'php_input_test_');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    // ===== 构造函数测试 =====

    public function testConstructorWithDefaultStream(): void
    {
        // 创建一个临时文件来模拟 php://input
        file_put_contents($this->tempFile, 'test input data');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getStreamResource() {
                return $this->resource;
            }
        };

        $this->assertInstanceOf(PhpInputStream::class, $stream);
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
    }

    public function testConstructorWithCustomStream(): void
    {
        file_put_contents($this->tempFile, 'custom stream data');

        $stream = new PhpInputStream($this->tempFile, 'r');

        $this->assertInstanceOf(PhpInputStream::class, $stream);
    }

    // ===== isWritable 方法测试 =====

    public function testIsWritableAlwaysReturnsFalse(): void
    {
        $stream = new PhpInputStream($this->tempFile, 'r');

        $this->assertFalse($stream->isWritable());

        // Test with different modes
        $stream2 = new PhpInputStream($this->tempFile, 'r+');
        $this->assertFalse($stream2->isWritable());
    }

    public function testWriteOperationThrowsException(): void
    {
        $stream = new PhpInputStream($this->tempFile, 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');
        $stream->write('test');
    }

    // ===== __toString 方法测试 =====

    public function testToStringReturnsCachedContentAfterEof(): void
    {
        file_put_contents($this->tempFile, 'test string content');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = (string)$stream;

        $this->assertSame('test string content', $content);

        // Second call should return cached content
        $secondContent = (string)$stream;
        $this->assertSame('test string content', $secondContent);
    }

    public function testToStringMultipleCalls(): void
    {
        file_put_contents($this->tempFile, 'multi call test');

        $stream = new PhpInputStream($this->tempFile, 'r');

        $firstCall = (string)$stream;
        $secondCall = (string)$stream;
        $thirdCall = (string)$stream;

        $this->assertSame('multi call test', $firstCall);
        $this->assertSame('multi call test', $secondCall);
        $this->assertSame('multi call test', $thirdCall);
    }

    // ===== read 方法测试 =====

    public function testReadCachesContent(): void
    {
        file_put_contents($this->tempFile, 'cached read test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $firstRead = $stream->read(5);  // 'cached'
        $secondRead = $stream->read(4); // ' read'

        $this->assertSame('cache', $firstRead);
        $this->assertSame('d re', $secondRead);
    }

    public function testReadUpdatesCacheAndEofFlag(): void
    {
        file_put_contents($this->tempFile, 'eof test');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getCache(): string {
                return $this->cache;
            }

            public function getEofStatus(): bool {
                return $this->reachedEof;
            }
        };

        $content = $stream->read(8); // Read all content

        $this->assertSame('eof test', $content);
        $this->assertSame('eof test', $stream->getCache());
        $this->assertFalse($stream->eof());

        $content = $stream->read(1); // Read all content
        $this->assertTrue($stream->getEofStatus());
    }

    public function testReadZeroLength(): void
    {
        file_put_contents($this->tempFile, 'zero read test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->read(0);

        $this->assertSame('', $content);
    }

    public function testReadNegativeLength(): void
    {
        $stream = new PhpInputStream($this->tempFile, 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Length must be non-negative');
        $stream->read(-5);
    }

    public function testReadBeyondEndOfFile(): void
    {
        file_put_contents($this->tempFile, 'short');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->read(20); // More than file length

        $this->assertSame('short', $content);
    }

    // ===== getContents 方法测试 =====

    public function testGetContentsReturnsCachedContentAfterEof(): void
    {
        file_put_contents($this->tempFile, 'cached contents test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $firstContents = $stream->getContents();
        $secondContents = $stream->getContents(); // Should return cached

        $this->assertSame('cached contents test', $firstContents);
        $this->assertSame('cached contents test', $secondContents);
    }

    public function testGetContentsWithMaxLength(): void
    {
        file_put_contents($this->tempFile, 'limited length test content');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $limitedContent = $stream->getContents(10); // Should read only first 10 chars

        $this->assertSame('limited le', $limitedContent);
    }

    public function testGetContentsWithNegativeMaxLength(): void
    {
        file_put_contents($this->tempFile, 'negative max test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->getContents(-1); // Default behavior, read all

        $this->assertSame('negative max test', $content);
    }

    public function testGetContentsUpdatesCache(): void
    {
        file_put_contents($this->tempFile, 'update cache test');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getCache(): string {
                return $this->cache;
            }
        };

        $content = $stream->getContents();

        $this->assertSame('update cache test', $content);
        $this->assertSame('update cache test', $stream->getCache());
    }

    // ===== 缓存机制测试 =====

    public function testCachePersistence(): void
    {
        file_put_contents($this->tempFile, 'persistent cache');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getCache(): string {
                return $this->cache;
            }

            public function getEofStatus(): bool {
                return $this->reachedEof;
            }
        };

        // Read partial content
        $stream->read(5); // 'persist'
        $this->assertSame('persi', $stream->getCache());
        $this->assertFalse($stream->getEofStatus());

        // Read rest of content
        $stream->read(100); // 'ent cache'
        $this->assertSame('persistent cache', $stream->getCache());
        $this->assertTrue($stream->getEofStatus());
    }

    public function testCacheAfterEofFlag(): void
    {
        file_put_contents($this->tempFile, 'cache after eof');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getCache(): string {
                return $this->cache;
            }
        };

        // Force EOF
        $stream->getContents();

        // Subsequent reads should not change cache
        $originalCache = $stream->getCache();
        $stream->read(1); // Should not add to cache

        $this->assertSame($originalCache, $stream->getCache());
    }

    // ===== 继承方法测试 =====

    public function testInheritedGetSize(): void
    {
        file_put_contents($this->tempFile, 'size test content');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $size = $stream->getSize();

        $this->assertSame(17, $size); // Length of 'size test content'
    }

    public function testInheritedTell(): void
    {
        file_put_contents($this->tempFile, 'tell test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $this->assertSame(0, $stream->tell());

        $stream->read(4); // Read 'tell'
        $this->assertSame(4, $stream->tell());
    }

    public function testInheritedEof(): void
    {
        file_put_contents($this->tempFile, 'eof');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $this->assertFalse($stream->eof());

        $stream->getContents(); // Read to end
        $this->assertTrue($stream->eof());
    }

    public function testInheritedSeekAndRewind(): void
    {
        file_put_contents($this->tempFile, 'seek test content');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $stream->read(4); // Move to position 4
        $this->assertSame(4, $stream->tell());

        $stream->rewind();
        $this->assertSame(0, $stream->tell());

        $stream->seek(5);
        $this->assertSame(5, $stream->tell());
    }

    public function testInheritedIsReadable(): void
    {
        $stream = new PhpInputStream($this->tempFile, 'r');
        $this->assertTrue($stream->isReadable());
    }

    public function testInheritedIsSeekable(): void
    {
        $stream = new PhpInputStream($this->tempFile, 'r');
        $this->assertTrue($stream->isSeekable());
    }

    // ===== 错误处理测试 =====

    public function testReadAfterDetach(): void
    {
        file_put_contents($this->tempFile, 'detach test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->read(5);
    }

    public function testGetContentsAfterDetach(): void
    {
        file_put_contents($this->tempFile, 'detach contents test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource available');
        $stream->getContents();
    }

    public function testToStringAfterDetach(): void
    {
        file_put_contents($this->tempFile, 'detach string test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $resource = $stream->detach();
        fclose($resource);

        $this->assertEquals('', (string)$stream);
    }

    // ===== 顺序操作测试 =====

    public function testSequentialReads(): void
    {
        file_put_contents($this->tempFile, 'sequential read test');

        $stream = new PhpInputStream($this->tempFile, 'r');

        $chunk1 = $stream->read(5);  // 'sequen'
        $chunk2 = $stream->read(4);  // 'tial'
        $chunk3 = $stream->read(5);  // ' read'
        $chunk4 = $stream->read(5);  // ' test'

        $this->assertSame('seque', $chunk1);
        $this->assertSame('ntia', $chunk2);
        $this->assertSame('l rea', $chunk3);
        $this->assertSame('d tes', $chunk4);
    }

    public function testMixedReadAndContents(): void
    {
        file_put_contents($this->tempFile, 'mixed operations test');

        $stream = new PhpInputStream($this->tempFile, 'r');

        // Read partial content
        $partial = $stream->read(6); // 'mixed '
        $this->assertSame('mixed ', $partial);

        // Get rest of content
        $rest = $stream->getContents();
        $this->assertSame('operations test', $rest);
    }

    public function testRewindResetsCache(): void
    {
        file_put_contents($this->tempFile, 'rewind cache test');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getCache(): string {
                return $this->cache;
            }
        };

        // Read all content to fill cache
        $allContent = $stream->getContents();
        $this->assertSame('rewind cache test', $allContent);
        $this->assertSame('rewind cache test', $stream->getCache());

        // Rewind and read again
        $stream->rewind();
        $reRead = $stream->read(6); // 'rewind'
        $this->assertSame('rewind', $reRead);
    }

    // ===== 特殊内容测试 =====

    public function testUnicodeContent(): void
    {
        $unicodeContent = "Hello 世界 🌍 测试";
        file_put_contents($this->tempFile, $unicodeContent);

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame($unicodeContent, $content);
    }

    public function testBinaryContent(): void
    {
        $binaryContent = "\x00\x01\x02\x03\xFF\xFE\xFD";
        file_put_contents($this->tempFile, $binaryContent);

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame($binaryContent, $content);
    }

    public function testEmptyContent(): void
    {
        file_put_contents($this->tempFile, '');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame('', $content);
        $this->assertTrue($stream->eof());
    }

    // ===== 大文件处理测试 =====

    public function testLargeContent(): void
    {
        $largeContent = str_repeat('A', 1024 * 10); // 10KB
        file_put_contents($this->tempFile, $largeContent);

        $stream = new PhpInputStream($this->tempFile, 'r');
        $content = $stream->getContents();

        $this->assertSame($largeContent, $content);
    }

    public function testLargeSequentialReads(): void
    {
        $largeContent = str_repeat('B', 1024 * 5); // 5KB
        file_put_contents($this->tempFile, $largeContent);

        $stream = new PhpInputStream($this->tempFile, 'r');

        $totalRead = '';
        while (!$stream->eof()) {
            $chunk = $stream->read(1024); // Read 1KB at a time
            $totalRead .= $chunk;
        }

        $this->assertSame($largeContent, $totalRead);
    }

    // ===== 元数据测试 =====

    public function testInheritedMetadata(): void
    {
        file_put_contents($this->tempFile, 'metadata test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $metadata = $stream->getMetadata();

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('uri', $metadata);
        $this->assertStringEndsWith($this->tempFile, $metadata['uri']);
    }

    public function testInheritedGetMode(): void
    {
        $stream = new PhpInputStream($this->tempFile, 'r+');
        $mode = $stream->getMode();

        $this->assertSame('r+', $mode);
    }

    // ===== 性能和边界测试 =====

    public function testSingleByteReads(): void
    {
        file_put_contents($this->tempFile, 'A');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $byte = $stream->read(1);

        $this->assertSame('A', $byte);
        $this->assertFalse($stream->eof());
        $byte = $stream->read(1);
        $this->assertTrue($stream->eof());
    }

    // ===== 缓存一致性测试 =====

    public function testCacheConsistencyAfterMultipleOperations(): void
    {
        file_put_contents($this->tempFile, 'consistency test content');

        $stream = new class($this->tempFile, 'r') extends PhpInputStream {
            public function getCache(): string {
                return $this->cache;
            }
        };

        // Multiple operations that should update cache consistently
        $firstRead = $stream->read(11); // 'consistency'
        $this->assertSame('consistency', $firstRead);
        $this->assertSame('consistency', $stream->getCache());

        $rest = $stream->getContents(); // ' test content'
        $this->assertSame(' test content', $rest);
        $this->assertSame('consistency test content', $stream->getCache());

        // String conversion should return full cached content
        $asString = (string)$stream;
        $this->assertSame('consistency test content', $asString);
    }

    // ===== 空流测试 =====

    public function testEmptyStreamOperations(): void
    {
        file_put_contents($this->tempFile, '');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $stream->read(1);
        $this->assertTrue($stream->eof());
        $this->assertSame('', $stream->getContents());
        $this->assertSame('', $stream->read(10));
        $this->assertSame('', (string)$stream);
    }

    // ===== 混合操作测试 =====

    public function testComplexOperationSequence(): void
    {
        $content = 'complex operation sequence test';
        file_put_contents($this->tempFile, $content);

        $stream = new PhpInputStream($this->tempFile, 'r');

        // Sequence: read -> getContents -> toString -> read
        $firstRead = $stream->read(7); // 'complex'
        $this->assertSame('complex', $firstRead);

        $stream->rewind();
        $allContent = $stream->getContents(); // 'complex operation sequence test'
        $this->assertSame($content, $allContent);

        $asString = (string)$stream; // Should return cached content
        $this->assertSame($content, $asString);

        $stream->rewind();
        $secondRead = $stream->read(7); // 'complex' again
        $this->assertSame('complex', $secondRead);
    }

    // ===== 析构函数测试 =====

    public function testDestructor(): void
    {
        file_put_contents($this->tempFile, 'destructor test');

        $stream = new PhpInputStream($this->tempFile, 'r');
        $resource = $stream->detach();

        // Force garbage collection
        $stream = null;
        $collected = gc_collect_cycles();

        // Should not cause errors
        $this->assertGreaterThanOrEqual(0, $collected);

        // Close the detached resource
        fclose($resource);
    }
}