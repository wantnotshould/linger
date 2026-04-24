<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

class Request
{
    protected array $query;
    protected array $body;
    protected array $server;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->server = $_SERVER;
    }

    protected function parseBody(): array
    {
        $body = $_POST;

        if (str_contains($this->header('Content-Type') ?? '', 'application/json')) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            return is_array($data) ? array_merge($body, $data) : $body;
        }

        return $body;
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim($path, '/');
    }

    public function query(?string $key = null, $default = null)
    {
        return $key ? ($this->query[$key] ?? $default) : $this->query;
    }

    public function input(?string $key = null, $default = null)
    {
        return $key ? ($this->body[$key] ?? $default) : $this->body;
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? null;
    }
}
