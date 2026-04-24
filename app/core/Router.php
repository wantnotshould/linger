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

    private static function callHandler(mixed $handler, array $params, Request $request): void
    {
        // 避免：Object of class app\core\Request could not be converted to int in 

        $container = Container::getInstance();
        $container->bind(Request::class, fn() => $request);

        if ($handler instanceof \Closure) {
            $args = self::resolveArgs($handler, $params, $request);
            $result = call_user_func_array($handler, $args);
        } else {
            [$class, $method] = explode('@', $handler, 2);
            $controller = $container->make($class);

            $args = self::resolveArgs([$controller, $method], $params, $request);
            $result = call_user_func_array([$controller, $method], $args);
        }

        if (!$result instanceof Response) {
            $result = new Response($result);
        }

        $result->send();
    }

    private static function resolveArgs(callable|array $callback, array $params, Request $request): array
    {
        // 如果是 [Controller, Method] 数组格式
        if (is_array($callback)) {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        } else {
            // 如果是匿名函数/闭包
            $reflection = new \ReflectionFunction($callback);
        }

        $finalArgs = [];
        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $name = $param->getName();

            if ($type && $type->getName() === Request::class) {
                $finalArgs[] = $request;
            } elseif (isset($params[$name])) {
                $finalArgs[] = $params[$name];
            } elseif (!empty($params)) {
                $finalArgs[] = array_shift($params);
            }
        }
        return $finalArgs;
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
