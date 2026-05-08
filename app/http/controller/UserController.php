<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\http\controller;

use app\core\Request;
use app\services\UserService;

class UserController extends BaseController
{
    protected UserService $userService;

    // 当 Router 执行 $container->make(UserController::class) 时
    // 它会自动发现这里需要一个 UserService 实例
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // 在 BaseController 中定义了 Request $request
    public function info(int $id)
    {
        $user = $this->userService->findUser($id);

        if (!$user) {
            return $this->error("用户不存在", 404);
        }

        return $this->success(['user' => $user]);
    }
}
