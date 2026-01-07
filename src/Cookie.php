<?php

declare(strict_types=1);

namespace FastD\Http;

use InvalidArgumentException;
use Stringable;

class Cookie implements Stringable
{
    public function __construct(
        protected string $name,
        protected string $value = '',
        protected int $expire = -1,
        protected string $path = '/',
        protected string $domain = '',
        protected bool $secure = false,
        protected bool $httpOnly = false
    ) {
        if (preg_match("/[=,; \t\r\n\v\f]/", $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
    }

    public function withName(string $name): static
    {
        if (preg_match("/[=,; \t\r\n\v\f]/", $name)) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function withValue(string $value): static
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }

    public function withExpire(int $expire): static
    {
        $new = clone $this;
        $new->expire = $expire;
        return $new;
    }

    public function withPath(string $path): static
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withDomain(string $domain): static
    {
        $new = clone $this;
        $new->domain = $domain;
        return $new;
    }

    public function withSecure(bool $secure): static
    {
        $new = clone $this;
        $new->secure = $secure;
        return $new;
    }

    public function withHttpOnly(bool $httpOnly): static
    {
        $new = clone $this;
        $new->httpOnly = $httpOnly;
        return $new;
    }

    // ===== 获取方法 (get) =====

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpire(): int
    {
        return $this->expire;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function __toString(): string
    {
        $str = urlencode($this->name) . '=';

        // 优化：使用 match 表达式判断是否为删除 cookie
        if ($this->value === '') {
            // 删除 cookie：空值 + 负过期时间
            $str .= 'deleted; expires=' . gmdate("D, d-M-Y H:i:s T", time() - 31536001);
        } else {
            // 正常值
            $str .= urlencode($this->value);

            // 添加过期时间（如果需要）
            if ($this->expire > 0) {
                $str .= '; expires=' . gmdate("D, d-M-Y H:i:s T", time() + $this->expire);
            }
        }

        if ($this->path !== '') {
            $str .= '; path=' . $this->path;
        }

        if ($this->domain !== '') {
            $str .= '; domain=' . $this->domain;
        }

        if ($this->secure) {
            $str .= '; secure';
        }

        if ($this->httpOnly) {
            $str .= '; httponly';
        }

        return $str;
    }

    public static function create(string $name, string $value = '', int $expire = -1, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): self
    {
        return new self($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}