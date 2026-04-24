<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container as IlluminateContainer;

class Database
{
    public static function boot(array $config)
    {
        $capsule = new Capsule;

        // 创建连接配置
        $capsule->addConnection([
            'driver'    => $config['driver'] ?? 'mysql',
            'host'      => $config['host'] ?? '127.0.0.1',
            'database'  => $config['database'],
            'username'  => $config['username'],
            'password'  => $config['password'],
            'charset'   => $config['charset'] ?? 'utf8mb4',
            'collation' => $config['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $config['prefix'] ?? '',
        ]);

        // 设置事件调度器
        $capsule->setEventDispatcher(new Dispatcher(new IlluminateContainer));

        // 设置为全局静态可访问
        $capsule->setAsGlobal();

        // 启动 Eloquent ORM
        $capsule->bootEloquent();
    }
}
