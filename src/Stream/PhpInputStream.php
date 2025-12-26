<?php
declare(strict_types=1);

namespace FastD\Http\Stream;

use RuntimeException;

class PhpInputStream extends Stream
{
    protected string $cache = '';

    /**
     * @var bool
     */
    protected bool $reachedEof = false;

    public function __construct(string $stream = 'php://input', string $mode = 'r')
    {
        parent::__construct($stream, $mode);
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->cache = '';
    }

    public function __toString(): string
    {
        if ($this->reachedEof || !$this->resource) {
            return $this->cache;
        }

        $this->rewind();
        $this->getContents();

        return $this->cache;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function read(int $length): string
    {
        $content = parent::read($length);

        if ($content && !$this->reachedEof) {
            $this->cache .= $content;
        }

        if ($this->eof()) {
            $this->reachedEof = true;
        }

        return $content;
    }

    public function getContents(?int $maxLength = -1): string
    {
        if ($this->reachedEof) {
            return $this->cache;
        }

        if (!$this->resource) {  // 修复：添加资源检查
            throw new RuntimeException('No resource available; cannot get contents');
        }

        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }

        $contents = stream_get_contents($this->resource, $maxLength);

        if (false === $contents) {
            throw new RuntimeException('Error reading from stream');
        }

        $this->cache .= $contents;

        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }

        return $contents;
    }
}
