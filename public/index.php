<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

use app\core\App;

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('PUBLIC', ROOT . '/public');

require_once ROOT . '/vendor/autoload.php';

$app = new App();
$app->run();
