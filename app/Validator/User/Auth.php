<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Name;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Auth
{

    public const password = "/(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*()_.]+)$)^[\w~!@#$%^&*()_.]{8,26}$/";
    public const username = "/^[a-zA-Z][a-zA-Z0-9]{1,15}$/";

    public function email(mixed $value): bool|string
    {
        if (!$value) {
            return true;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '邮箱格式错误';
        }
        return true;
    }

    #[Name("username")]
    #[Required("用户名不能为空")]
    #[Regex(Auth::username, "用户名必须是2-16位字母，可带数字，但必须字母开头")]
    public function registerUsername(): bool
    {
        return true;
    }


    #[Name("password")]
    #[Required("请设置你的登录密码")]
    #[Regex(Auth::password, "登录密码应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串")]
    public function registerPassword(): bool
    {
        return true;
    }


    #[Name("username")]
    #[Required("用户名不能为空")]
    public function loginUsername(): bool
    {
        return true;
    }


    #[Name("password")]
    #[Required("登录密码不能为空")]
    public function loginPassword(): bool
    {
        return true;
    }


    #[Name("email")]
    #[Required("邮箱不能为空")]
    public function sendEmail(mixed $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '邮箱格式错误';
        }
        return true;
    }

    #[Name("email_code")]
    #[Required("邮箱验证码不能为空")]
    #[Regex("/^\d{6}$/", "邮箱验证码错误")]
    public function resetEmailCode(mixed $value): bool|string
    {
        return true;
    }

}