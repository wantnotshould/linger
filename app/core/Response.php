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

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        echo is_array($this->content) ? json_encode($this->content) : $this->content;
    }
}
