<?php
declare (strict_types=1);

namespace App\Validator\Install;

use App\Validator\User\Auth;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Finish
{
    #[Required("呢称不能为空")]
    public function loginNickname(): bool
    {
        return true;
    }


    #[Required("邮箱不能为空")]
    public function loginEmail(mixed $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '邮箱格式错误';
        }
        return true;
    }


    #[Required("请设置你的登录密码")]
    #[Regex(Auth::password, "登录密码应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串")]
    public function loginPassword(): bool
    {
        return true;
    }

    #[Required("请再次输入你的登录密码")]
    public function loginRePassword(mixed $value, array $post): bool|string
    {
        if ($value !== $post['login_password']) {
            return '两次密码不一致';
        }
        return true;
    }

}