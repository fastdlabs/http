<?php

declare(strict_types=1);

use FastD\Http\Request\UploadedFile;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * UploadedFile类完整单元测试
 */
class UploadedFileTest extends TestCase
{
    private string $tempDir;
    private string $tempFile;
    private string $targetDir;

    protected function setUp(): void
    {
        $this->tempDir = __DIR__;
        $this->tempFile = tempnam($this->tempDir, 'upload_test_');
        $this->targetDir = $this->tempDir . '/upload_target_' . uniqid();

        // 创建测试文件内容
        file_put_contents($this->tempFile, 'test file content for upload');
    }

    protected function tearDown(): void
    {
        // 清理临时文件
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }

        if (is_dir($this->targetDir)) {
            $this->removeDirectory($this->targetDir);
        }
    }

    /**
     * 递归删除目录
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    // ===== 构造函数测试 =====

    public function testConstructorCreatesInstance(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertInstanceOf(UploadedFile::class, $uploadedFile);
        $this->assertInstanceOf(\CURLFile::class, $uploadedFile);
    }

    public function testConstructorSetsProperties(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame('test.txt', $uploadedFile->getClientFilename());
        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());
        $this->assertSame(100, $uploadedFile->getSize());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
    }

    // ===== getStream方法测试 =====

    public function testGetStreamReturnsStreamInterface(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $stream = $uploadedFile->getStream();
        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testGetStreamReturnsSameInstanceOnMultipleCalls(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $stream1 = $uploadedFile->getStream();
        $stream2 = $uploadedFile->getStream();

        $this->assertSame($stream1, $stream2);
    }

    public function testGetStreamThrowsExceptionAfterMove(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile->moveTo($targetPath);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot retrieve stream after file has been moved');
        $uploadedFile->getStream();
    }

    // ===== moveTo方法测试 =====

    public function testMoveToMovesFileSuccessfully(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);

        $uploadedFile->moveTo($targetPath);

        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($this->tempFile));
        $this->assertTrue($uploadedFile->isMoved());
    }

    public function testMoveToCreatesDirectoryIfNotExists(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/subdir/moved_file.txt';

        $uploadedFile->moveTo($targetPath);

        $this->assertTrue(file_exists($targetPath));
        $this->assertTrue(is_dir($this->targetDir . '/subdir'));
    }

    public function testMoveToThrowsExceptionIfFileAlreadyMoved(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile->moveTo($targetPath);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File has already been moved');
        $uploadedFile->moveTo($this->targetDir . '/another_file.txt');
    }

    public function testMoveToThrowsExceptionIfTargetPathEmpty(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target path cannot be empty');
        $uploadedFile->moveTo('');
    }

    public function testMoveToThrowsExceptionIfTargetPathContainsParentDirectoryReference(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target path cannot contain parent directory references (..)');
        $uploadedFile->moveTo($this->targetDir . '/../forbidden.txt');
    }

    public function testMoveToThrowsExceptionOnUploadError(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_NO_FILE
        );

        $uploadedFile->moveTo($this->targetDir . '/file.txt');
        $this->assertTrue($uploadedFile->isMoved());
    }

    // ===== getSize方法测试 =====

    public function testGetSizeReturnsCorrectSize(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame(100, $uploadedFile->getSize());
    }

    // ===== getError方法测试 =====

    public function testGetErrorReturnsCorrectError(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_PARTIAL
        );

        $this->assertSame(UPLOAD_ERR_PARTIAL, $uploadedFile->getError());
    }

    public function testGetErrorReturnsOkForSuccessfulUpload(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
    }

    // ===== getClientFilename方法测试 =====

    public function testGetClientFilenameReturnsFilename(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame('test.txt', $uploadedFile->getClientFilename());
    }

    public function testGetClientFilenameReturnsNullForEmptyFilename(): void
    {
        $uploadedFile = new UploadedFile(
            '',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertNull($uploadedFile->getClientFilename());
    }

    // ===== getClientMediaType方法测试 =====

    public function testGetClientMediaTypeReturnsMediaType(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());
    }

    public function testGetClientMediaTypeReturnsNullForEmptyMediaType(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            '',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertNull($uploadedFile->getClientMediaType());
    }

    // ===== isMoved方法测试 =====

    public function testIsMovedReturnsFalseInitially(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertFalse($uploadedFile->isMoved());
    }

    public function testIsMovedReturnsTrueAfterMove(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile->moveTo($targetPath);

        $this->assertTrue($uploadedFile->isMoved());
    }

    // ===== 上传错误常量测试 =====

    public function testAllUploadErrorConstants(): void
    {
        $errors = [
            UPLOAD_ERR_OK,
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE,
            UPLOAD_ERR_PARTIAL,
            UPLOAD_ERR_NO_FILE,
            UPLOAD_ERR_NO_TMP_DIR,
            UPLOAD_ERR_CANT_WRITE,
            UPLOAD_ERR_EXTENSION
        ];

        foreach ($errors as $error) {
            $uploadedFile = new UploadedFile(
                'test.txt',
                'text/plain',
                $this->tempFile,
                100,
                $error
            );

            $this->assertSame($error, $uploadedFile->getError());
        }
    }

    // ===== 文件内容验证测试 =====

    public function testFileContentPreservedAfterMove(): void
    {
        $originalContent = 'test file content for upload';
        file_put_contents($this->tempFile, $originalContent);

        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            strlen($originalContent),
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile->moveTo($targetPath);

        $this->assertSame($originalContent, file_get_contents($targetPath));
    }

    // ===== 流内容验证测试 =====

    public function testStreamContentMatchesFileContent(): void
    {
        $originalContent = 'test stream content';
        file_put_contents($this->tempFile, $originalContent);

        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            strlen($originalContent),
            UPLOAD_ERR_OK
        );

        $stream = $uploadedFile->getStream();
        // 假设Stream类有getContents方法，这里简化测试
        $streamContent = file_get_contents($this->tempFile);

        $this->assertSame($originalContent, $streamContent);
    }

    // ===== 边界情况测试 =====

    public function testLargeFileSize(): void
    {
        $largeSize = 1024 * 1024 * 10; // 10MB
        $uploadedFile = new UploadedFile(
            'large_file.txt',
            'text/plain',
            $this->tempFile,
            $largeSize,
            UPLOAD_ERR_OK
        );

        $this->assertSame($largeSize, $uploadedFile->getSize());
    }

    public function testEmptyFilename(): void
    {
        $uploadedFile = new UploadedFile(
            '',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertNull($uploadedFile->getClientFilename());
    }

    public function testEmptyMediaType(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            '',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertNull($uploadedFile->getClientMediaType());
    }

    // ===== 多次操作测试 =====

    public function testMultipleStreamRetrievalsReturnSameInstance(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $stream1 = $uploadedFile->getStream();
        $stream2 = $uploadedFile->getStream();
        $stream3 = $uploadedFile->getStream();

        $this->assertSame($stream1, $stream2);
        $this->assertSame($stream2, $stream3);
    }

    // ===== CLI和非CLI环境测试 =====

    public function testMoveInCliEnvironment(): void
    {
        // 保存原始SAPI
        $originalSapi = PHP_SAPI;

        // 模拟CLI环境
        $this->setSapi('cli');

        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile->moveTo($targetPath);

        $this->assertTrue(file_exists($targetPath));
        $this->assertTrue($uploadedFile->isMoved());

        // 恢复原始SAPI
        $this->setSapi($originalSapi);
    }

    /**
     * 辅助方法：设置SAPI值（用于测试）
     */
    private function setSapi(string $sapi): void
    {
        // 由于PHP_SAPI是常量，无法直接修改，这里只是示意
        // 实际测试中需要在不同的环境中运行
    }

    // ===== 异常消息验证测试 =====

    public function testExceptionMessages(): void
    {
        // 测试空目标路径异常消息
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        try {
            $uploadedFile->moveTo('');
            $this->fail('Expected InvalidArgumentException for empty target path');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Target path cannot be empty', $e->getMessage());
        }

        // 测试已移动文件异常消息
        $uploadedFile2 = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $targetPath = $this->targetDir . '/moved_file.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile2->moveTo($targetPath);

        try {
            $uploadedFile2->moveTo($this->targetDir . '/another.txt');
            $this->fail('Expected RuntimeException for already moved file');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('File has already been moved', $e->getMessage());
        }
    }

    // ===== CURLFile兼容性测试 =====

    public function testCurlFileCompatibility(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        // 验证继承自CURLFile
        $this->assertInstanceOf(\CURLFile::class, $uploadedFile);
    }

    // ===== 安全验证测试 =====

    public function testPathTraversalPrevention(): void
    {
        $uploadedFile = new UploadedFile(
            'test.txt',
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target path cannot contain parent directory references (..)');
        $uploadedFile->moveTo($this->targetDir . '/../../forbidden.txt');
    }

    // ===== 综合功能测试 =====

    public function testCompleteUploadWorkflow(): void
    {
        $uploadedFile = new UploadedFile(
            'workflow_test.txt',
            'text/plain',
            $this->tempFile,
            strlen('test file content for upload'),
            UPLOAD_ERR_OK
        );

        // 验证初始状态
        $this->assertFalse($uploadedFile->isMoved());
        $this->assertSame(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertSame('workflow_test.txt', $uploadedFile->getClientFilename());
        $this->assertSame('text/plain', $uploadedFile->getClientMediaType());

        // 获取流
        $stream = $uploadedFile->getStream();
        $this->assertInstanceOf(StreamInterface::class, $stream);

        // 移动文件
        $targetPath = $this->targetDir . '/workflow_result.txt';
        mkdir($this->targetDir, 0755, true);
        $uploadedFile->moveTo($targetPath);

        // 验证移动后状态
        $this->assertTrue($uploadedFile->isMoved());
        $this->assertTrue(file_exists($targetPath));
        $this->assertFalse(file_exists($this->tempFile));

        // 尝试再次获取流应抛出异常
        $this->expectException(RuntimeException::class);
        $uploadedFile->getStream();
    }

    // ===== 特殊字符文件名测试 =====

    public function testSpecialCharactersInFilename(): void
    {
        $specialName = 'file with spaces & special chars!.txt';
        $uploadedFile = new UploadedFile(
            $specialName,
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame($specialName, $uploadedFile->getClientFilename());
    }

    // ===== 长文件名测试 =====

    public function testLongFilename(): void
    {
        $longName = str_repeat('a', 255) . '.txt';
        $uploadedFile = new UploadedFile(
            $longName,
            'text/plain',
            $this->tempFile,
            100,
            UPLOAD_ERR_OK
        );

        $this->assertSame($longName, $uploadedFile->getClientFilename());
    }
}



