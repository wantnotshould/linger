<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\http\controller;

use app\core\Request;

class IndexController extends BaseController
{
    public function index()
    {
        echo 'hello, IndexController@index';
    }

    public function info(Request $request, $id)
    {
        return $this->success([
            'controller' => 'IndexController@info',
            'params' => $request->all(),
            'header' => $request->allHeaders(),
            'full_url' => $request->fullUrl(),
        ]);
    }
}
