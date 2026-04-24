<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

class App
{
    public function __construct()
    {
        try {
            Env::load(ROOT . '/.env');
        } catch (\Throwable $e) {
            http_response_code(500);
            exit("env load failed: " . $e->getMessage());
        }
    }

    public function boot(): void
    {
        require_once ROOT . '/routes/app.php';
    }

    public function run(): void
    {
        $this->boot();
    }
}
