<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\services;

use app\models\User;

class UserService
{
    public function findUser(int $id)
    {
        return User::find($id);
    }
}
