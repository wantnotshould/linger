<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

class Response
{
    protected int $statusCode = 200;
    protected array $headers = [];
    protected mixed $content;
    protected array $cookies = [];

    public function __construct(mixed $content = '', int $statusCode = 200)
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public static function json(array $data, int $code = 200): self
    {
        $response = new static(json_encode($data), $code);
        $response->header('Content-Type', 'application/json');
        return $response;
    }

    public function withCookie(
        string $name,
        string $value,
        int $minutes = 60,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    ): self {
        $expiry = time() + ($minutes * 60);
        $this->cookies[] = compact('name', 'value', 'expiry', 'path', 'domain', 'secure', 'httpOnly');
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        foreach ($this->cookies as $c) {
            setcookie(
                $c['name'],
                $c['value'],
                $c['expiry'],
                $c['path'],
                $c['domain'],
                $c['secure'],
                $c['httpOnly']
            );
        }

        echo is_array($this->content) ? json_encode($this->content) : $this->content;
    }
}
