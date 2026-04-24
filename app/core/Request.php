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
    protected array $files;
    protected array $cookies;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
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

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function query(?string $key = null, $default = null)
    {
        return $key ? ($this->query[$key] ?? $default) : $this->query;
    }

    public function input(string $key, $default = null, bool $trim = true)
    {
        $data = $this->all();
        $value = $data[$key] ?? $default;

        if ($trim && is_string($value)) {
            $value = trim($value);
        }

        return $value;
    }

    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST') {
            $spoofedMethod = strtoupper($this->body['_method'] ?? $this->header('X-HTTP-METHOD-OVERRIDE') ?? '');

            if (in_array($spoofedMethod, ['PUT', 'DELETE', 'PATCH'])) {
                return $spoofedMethod;
            }
        }

        return $method;
    }

    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim($path, '/');
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function header(string $key): ?string
    {
        $key = str_replace('-', '_', strtoupper($key));
        if (!str_starts_with($key, 'HTTP_') && $key !== 'CONTENT_TYPE' && $key !== 'CONTENT_LENGTH') {
            $key = 'HTTP_' . $key;
        }
        return $this->server[$key] ?? null;
    }

    public function allHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            // 处理以 HTTP_ 开头的字段
            if (str_starts_with($key, 'HTTP_')) {
                // 去掉 HTTP_ 前缀，将下划线转回中划线，转为小写
                // 例如: HTTP_USER_AGENT -> user-agent
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
            // 特殊处理 Content-Type 和 Content-Length
            elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $name = str_replace('_', '-', strtolower($key));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function fullUrl(): string
    {
        $protocol = $this->header('HTTPS') === 'on' ? 'https' : 'http';
        return $protocol . "://" . $this->header('HOST') . $this->server['REQUEST_URI'];
    }

    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    public function allCookies(): array
    {
        return $this->cookies;
    }
}
