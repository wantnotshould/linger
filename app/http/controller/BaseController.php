<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\http\controller;

use app\core\Request;
use app\traits\ResponseTrait;

class BaseController
{
    use ResponseTrait;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
