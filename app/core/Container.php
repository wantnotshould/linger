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
    protected array $instances = [];
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
        // 如果已经有现成的单例（比如之前 bind 进去的实例），直接返回
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // 如果有绑定逻辑（闭包），执行绑定逻辑
        if (isset($this->bindings[$abstract]) && $this->bindings[$abstract] instanceof \Closure) {
            return call_user_func($this->bindings[$abstract], ...$parameters);
        }

        return $this->resolve($abstract, $parameters);
    }

    protected function resolve(string $abstract, array $parameters = [])
    {
        try {
            $reflection = new \ReflectionClass($abstract);
        } catch (\ReflectionException $e) {
            throw new \Exception("target class [$abstract] does not exist.");
        }

        if (!$reflection->isInstantiable()) {
            throw new \Exception("target class [$abstract] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if (is_null($constructor)) {
            return new $abstract;
        }

        $dependencies = $constructor->getParameters();
        $instances = [];

        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();
            $name = $dependency->getName();

            // 如果是类，递归解析
            if ($type && !$type->isBuiltin()) {
                $instances[] = $this->make($type->getName());
            }
            // 如果是内置类型（int/string），尝试从 $parameters 找
            elseif (array_key_exists($name, $parameters)) {
                $instances[] = $parameters[$name];
            }
            // 如果有默认值，用默认值
            elseif ($dependency->isDefaultValueAvailable()) {
                $instances[] = $dependency->getDefaultValue();
            }
            // 报错
            else {
                throw new \Exception("unresolvable dependency [{$name}] in class [{$abstract}]");
            }
        }

        return $reflection->newInstanceArgs($instances);
    }
}
