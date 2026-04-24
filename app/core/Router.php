<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

class Router
{
    protected static array $routes = [];
    protected static array $groupStack = [];

    public static function group(array $attributes, callable $callback): void
    {
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];

        if (!empty(self::$groupStack)) {
            $parent = end(self::$groupStack);
            $middleware = array_merge($parent['middleware'] ?? [], $middleware);
        }

        self::$groupStack[] = [
            'prefix' => trim($prefix, '/'),
            'middleware' => $middleware
        ];

        $callback();
        array_pop(self::$groupStack);
    }

    private static function addRoute(string $method, string $path, mixed $handler): void
    {
        $prefix = '';
        $middleware = [];

        if (!empty(self::$groupStack)) {
            $group = end(self::$groupStack);
            $prefix = $group['prefix'] ?? '';
            $middleware = $group['middleware'] ?? [];
        }

        $finalPath = '/' . trim(trim($prefix, '/') . '/' . trim($path, '/'), '/');
        if ($finalPath === '//') {
            $finalPath = '/';
        }

        self::$routes[$method][] = [
            'path' => $finalPath === '' ? '/' : $finalPath,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private static function callHandler(mixed $handler, array $params, Request $request): Response
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

        // 这里不再直接 send()，而是返回 Response 对象让管道流转
        return $result;
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
                    'middleware' => $route['middleware'],
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

        // 中间件
        $middleware = $route['middleware'];

        // 模拟 Laravel 的洋葱模型
        // 最终目的地是执行 Controller 逻辑
        $destination = function (Request $currentRequest) use ($route) {
            return self::callHandler($route['handler'], $route['params'], $currentRequest);
        };

        // 倒序排列中间件，确保顺序执行
        $pipeline = array_reduce(
            array_reverse($middleware),
            function ($stack, $pipe) {
                return function (Request $request) use ($stack, $pipe) {
                    $instance = Container::getInstance()->make($pipe);
                    return $instance->handle($request, $stack);
                };
            },
            $destination
        );

        try {
            $response = $pipeline($request);
            if (!$response instanceof Response) {
                $response = new Response($response);
            }

            $response->send();
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => false,
                'message' => 'Server Error: ' . $e->getMessage(),
                'code' => 500,
                'data' => null
            ], JSON_UNESCAPED_UNICODE);
        }
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
