<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\middleware;

use app\core\Request;
use app\core\Response;

class AuthMiddleware
{
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->cookie('auth_token')) {
            return new Response(json_encode(['status' => false, 'msg' => 'Unauthorized']), 401);
        }

        // 执行下一个中间件或控制器
        $response = $next($request);

        // 可以在这里修改 Response
        return $response;
    }
}
