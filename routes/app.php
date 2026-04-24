<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

use app\core\Router;
use app\http\controller\IndexController;
use app\http\controller\UserController;
use app\middleware\AuthMiddleware;

Router::get('/', function () {
    echo 'linger';
});

Router::get('index', IndexController::class . '@index');
Router::get('info/{id}', IndexController::class . '@info');
Router::get('test-cookie', IndexController::class . '@cookie');

Router::group(['prefix' => 'user', 'middleware' => [AuthMiddleware::class]], function () {
    Router::get('info/{id}', UserController::class . '@info');
});
