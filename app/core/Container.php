<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

use Exception;
use ReflectionClass;

class Container
{
    protected array $bindings = [];
    protected static ?Container $instance = null;

    public static function getInstance(): Container
    {
        // 这里使用 static 是为了允许子类拥有自己独立的单例实例
        // 使用 self::$instance，所有子类都会共享父类那一个静态变量
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function bind(string $abstract, $concrete = null): void
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }
        $this->bindings[$abstract] = $concrete;
    }

    public function make(string $abstract, array $parameters = [])
    {
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract] instanceof \Closure) {
            return call_user_func($this->bindings[$abstract], ...$parameters);
        }

        return $this->resolve($abstract);
    }

    protected function resolve(string $abstract)
    {
        $reflection = new ReflectionClass($abstract);

        if (!$reflection->isInstantiable()) {
            throw new Exception("target class [$abstract] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if (is_null($constructor)) {
            return new $abstract;
        }

        $dependencies = $constructor->getParameters();
        $instances = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();
            if ($type && !$type->isBuiltin()) {
                $instances[] = $this->make($type->getName());
            }
        }

        return $reflection->newInstanceArgs($instances);
    }
}
