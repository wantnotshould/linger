<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

use Exception;

class Router
{
    protected static array $routes = [];
    protected static array $groupStack = [];

    public static function group(string $prefix, callable $callback): void
    {
        self::$groupStack[] = ['prefix' => trim($prefix, '/')];
        $callback();
        array_pop(self::$groupStack);
    }

    private static function addRoute(string $method, string $path, mixed $handler): void
    {
        $prefix = '';
        if (!empty(self::$groupStack)) {
            $group = end(self::$groupStack);
            $prefix = $group['prefix'] ?? '';
        }

        $finalPath = '/' . trim(trim($prefix, '/') . '/' . trim($path, '/'), '/');
        if ($finalPath === '//') {
            $finalPath = '/';
        }

        self::$routes[$method][] = [
            'path' => $finalPath === '' ? '/' : $finalPath,
            'handler' => $handler,
        ];
    }

    private static function method(): string
    {
        if (isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    private static function callHandler(mixed $handler, array $params, Request $request): void
    {
        $container = Container::getInstance();
        $container->bind(Request::class, fn() => $request);

        if ($handler instanceof \Closure) {
            $args = array_merge([$request], array_values($params));
            $result = call_user_func_array($handler, $args);
            if ($result !== null) {
                echo $result;
            }
            return;
        }

        if (!is_string($handler) || !str_contains($handler, '@')) {
            throw new Exception('format invalid. Use Controller@Method.');
        }

        [$class, $method] = explode('@', $handler, 2);
        if (!class_exists($class)) {
            throw new Exception("class [$class] not found.");
        }

        $controller = $container->make($class);

        if (!method_exists($controller, $method)) {
            throw new Exception("method [$method] not found in [$class].");
        }
        
        $args = array_merge([$request], array_values($params));
        $response = call_user_func_array([$controller, $method], $args);

        if ($response !== null) {
            echo $response;
        }
    }

    private static function match(string $method, string $uri): ?array
    {
        foreach (self::$routes[$method] ?? [] as $route) {
            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                return [
                    'handler' => $route['handler'],
                    'params' => $params,
                ];
            }
        }
        return null;
    }

    public static function dispatch(): void
    {
        $request = new Request();
        $method = $request->method();
        $uri = $request->path();

        $route = self::match($method, $uri);

        if (!$route) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        self::callHandler($route['handler'], $route['params'], $request);
    }

    public static function get(string $path, mixed  $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, mixed $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    public static function put(string $path, mixed $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    public static function delete(string $path, mixed $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    public static function patch(string $path, mixed $handler): void
    {
        self::addRoute('PATCH', $path, $handler);
    }
}
