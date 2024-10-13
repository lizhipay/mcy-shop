<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use App\Validator\User\Auth;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class User
{

    #[Regex(Auth::password, "登录密码应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串")]
    public function password(): bool
    {
        return true;
    }
}