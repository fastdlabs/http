<?php

declare(strict_types=1);

namespace FastD\Http\Request;

use CURLFile;
use FastD\Http\Stream\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile extends CURLFile implements UploadedFileInterface
{
    protected bool $moved = false;

    public function __construct(
        protected string $clientFilename,
        protected string $clientMediaType,
        protected string $tmpName,
        protected int $size,
        protected int $error,
        protected ?StreamInterface $stream = null,
    ) {
        // Initialize parent CURLFile
        parent::__construct($tmpName, $this->clientMediaType, $this->clientFilename);
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve stream after file has been moved');
        }

        if ($this->stream !== null) {
            return $this->stream;
        }

        try {
            $this->stream = new Stream($this->tmpName, 'r');
            return $this->stream;
        } catch (\Exception $e) {
            throw new RuntimeException('Cannot create file stream: ' . $e->getMessage());
        }
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @return string
     * @throws InvalidArgumentException if the $targetPath specified is invalid.
     * @throws RuntimeException on any error during the move operation, or on
     *                           the second or subsequent call to the method.
     */
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('File has already been moved');
        }

        if ($targetPath === '') {
            throw new InvalidArgumentException('Target path cannot be empty');
        }

        if (str_contains($targetPath, '..')) {
            throw new InvalidArgumentException('Target path cannot contain parent directory references (..)');
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new RuntimeException('Cannot create target directory: ' . $targetDir);
            }
        }

        $realPath = realpath($targetDir);
        if ($realPath === false || !str_starts_with($targetDir, $realPath)) {
            throw new InvalidArgumentException('Target path is not within allowed directory scope');
        }

        if ('cli' === PHP_SAPI) {
            $success = rename($this->tmpName, $targetPath);
        } else {
            if (!is_uploaded_file($this->tmpName)) {
                throw new RuntimeException('File is not a valid uploaded file');
            }
            $success = move_uploaded_file($this->tmpName, $targetPath);
        }

        if (!$success) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        $this->moved = true;
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename !== '' ? $this->clientFilename : null;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType !== '' ? $this->clientMediaType : null;
    }

    /**
     * Check if file has been moved
     *
     * @return bool True if file has been moved, false otherwise
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }
}
