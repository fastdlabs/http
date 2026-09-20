<?php

declare(strict_types=1);

namespace FastD\Http\Session\Handler;

use FastD\Http\Session\SessionHandlerInterface;
use RuntimeException;

/**
 * Filesystem-backed session handler for the CGI runtime.
 *
 * Payloads are stored as JSON files, one per session id, under the configured
 * save path. Expiration is tracked through the file modification time and
 * expired entries are removed lazily on access.
 */
class NativeFileSessionHandler implements SessionHandlerInterface
{
    private string $savePath;

    public function __construct(string $savePath = '', private int $ttl = 1440)
    {
        if ($savePath === '') {
            $savePath = (string) session_save_path();
        }

        // session.save_path may carry a leading "N;" or trailing ";mode" directive.
        if (str_contains($savePath, ';')) {
            $parts = explode(';', $savePath);
            $savePath = (string) end($parts);
        }

        if ($savePath === '') {
            $savePath = sys_get_temp_dir();
        }

        $this->savePath = rtrim($savePath, DIRECTORY_SEPARATOR);

        if (!is_dir($this->savePath) && !@mkdir($this->savePath, 0700, true) && !is_dir($this->savePath)) {
            throw new RuntimeException(sprintf('Session save path "%s" is not available.', $this->savePath));
        }
    }

    public function read(string $sessionId): array
    {
        $file = $this->file($sessionId);

        if (!$this->isFresh($file)) {
            return [];
        }

        $content = @file_get_contents($file);

        if ($content === false || $content === '') {
            return [];
        }

        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    public function write(string $sessionId, array $data): bool
    {
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($content === false) {
            return false;
        }

        $file = $this->file($sessionId);
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';

        if (@file_put_contents($tmp, $content) === false) {
            return false;
        }

        @chmod($tmp, 0600);

        if (!@rename($tmp, $file)) {
            @unlink($tmp);

            return false;
        }

        return true;
    }

    public function destroy(string $sessionId): bool
    {
        $file = $this->file($sessionId);

        if (!is_file($file)) {
            return false;
        }

        return @unlink($file);
    }

    public function exists(string $sessionId): bool
    {
        return $this->isFresh($this->file($sessionId));
    }

    private function file(string $sessionId): string
    {
        // Never let an untrusted id escape the save path.
        $safe = preg_match('/^[A-Za-z0-9]{1,128}$/', $sessionId) === 1
            ? $sessionId
            : hash('sha256', $sessionId);

        return $this->savePath . DIRECTORY_SEPARATOR . 'fastd_session_' . $safe;
    }

    private function isFresh(string $file): bool
    {
        if (!is_file($file)) {
            return false;
        }

        $mtime = @filemtime($file);

        if ($mtime === false) {
            return false;
        }

        if ($mtime + $this->ttl <= time()) {
            @unlink($file);

            return false;
        }

        return true;
    }
}
