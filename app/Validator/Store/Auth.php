<?php
declare (strict_types=1);

namespace App\Validator\Store;

use Kernel\Annotation\Name;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Util\Verify;

class Auth
{

    public const password = "/(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*()_.]+)$)^[\w~!@#$%^&*()_.]{8,26}$/";
    public const username = "/^[a-zA-Z][a-zA-Z0-9]{1,15}$/";

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


    #[Name("username")]
    #[Required("用户名不能为空")]
    #[Regex(Auth::username, "用户名必须是2-16位字母，可带数字，但必须字母开头")]
    public function registerUsername(string $value): bool|string
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

    #[Name("phone")]
    #[Required("手机号不能为空")]
    public function phone(string $value): bool|string
    {
        if (!Verify::isChinaMobile($value) && !Verify::isInternationalMobile($value)) {
            return "手机号格式错误";
        }
        return true;
    }


    #[Required("图形验证码不能为空")]
    #[Regex("/^.{4}$/", "图形验证码错误")]
    public function captcha(): bool
    {
        return true;
    }


    #[Name("phone")]
    #[Required("手机号不能为空")]
    public function sendSms(string $value): bool|string
    {
        if (!Verify::isChinaMobile($value) && !Verify::isInternationalMobile($value)) {
            return "手机号格式错误";
        }
        return true;
    }


    #[Required("短信类型不能为空")]
    #[Regex("/^(register|reset)$/", "短信类型错误")]
    public function type(): bool
    {
        return true;
    }


    #[Required("手机验证码不能为空")]
    #[Regex("/^\d{6}$/", "手机验证码错误")]
    public function code(): bool
    {
        return true;
    }

}