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
            'id' => $id
        ]);
    }

    public function cookie(Request $request)
    {
        $oldValue = $request->cookie('test_key', 'default_value');

        if ($request->query('action') === 'update') {
            return $this->success(['old_value' => $oldValue])
                ->withCookie('test_key', 'new_secret_123', 60);
        }

        if ($request->query('action') === 'delete') {
            return $this->expireCookie('test_key');
        }

        return $this->success([
            'message' => 'Cookie has been set!',
            'current_cookie_in_request' => $oldValue
        ])->withCookie('test_key', 'initial_value_888', 60);
    }
}
