<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

use app\core\Router;
use app\http\controller\IndexController;

Router::get('/', function () {
    echo 'linger';
});

Router::get('index', IndexController::class . '@index');
