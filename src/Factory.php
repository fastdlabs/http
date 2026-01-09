<?php

declare(strict_types=1);

namespace FastD\Http;

use FastD\Http\Request\Request;
use FastD\Http\Request\ServerRequest;
use FastD\Http\Request\UploadedFile;
use FastD\Http\Response\Text;
use FastD\Http\Stream\Stream;
use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class Factory implements RequestFactoryInterface, ResponseFactoryInterface, ServerRequestFactoryInterface, StreamFactoryInterface, UriFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        if ($uri instanceof UriInterface) {
            $uri = (string)$uri;
        }
        return new Request($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Text($code, '');
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if ($uri instanceof UriInterface) {
            $uri = (string)$uri;
        }
        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::create($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return new Stream($filename, $mode);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        if ((stream_get_meta_data($resource)['uri'] ?? '') === 'php://input') {
            return new Stream('php://temp', 'w+');
        }

        return new Stream('php://temp', 'r+');
    }

    public function createUploadedFile(
        StreamInterface $stream,
        ?int            $size = null,
        int             $error = \UPLOAD_ERR_OK,
        ?string         $clientFilename = null,
        ?string         $clientMediaType = null
    ): UploadedFileInterface
    {
        if ($size === null) {
            $size = $stream->getSize();
        }

        if (empty($clientFilename)) {
            throw new InvalidArgumentException('Client uploaded file name cannot be empty');
        }

        // 为了兼容 UploadedFile 的构造函数，我们需要一个临时文件路径
        // 当从 stream 创建时，我们将 stream 写入临时文件
        $tmpFile = tempnam(sys_get_temp_dir(), 'curl_upload_tmp_');
        
        // 将 stream 内容写入临时文件
        file_put_contents($tmpFile, $stream->getContents());

        return new UploadedFile(
            $clientFilename,
            $clientMediaType ?? '',
            $tmpFile,
            $size,
            $error,
            $stream
        );
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}