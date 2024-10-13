<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Manage
{

    /**
     * @param mixed $value
     * @return bool|string
     */
    public function id(mixed $value): bool|string
    {
        if ($value > 0 && !\App\Model\Manage::where("id", (int)$value)->exists()) {
            return '管理员不存在';
        }
        return true;
    }


    #[Required("邮箱不能为空")]
    public function email(string $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '邮箱格式错误';
        }
        return true;
    }


    #[Required("呢称不能为空")]
    public function nickname(): bool
    {
        return true;
    }


    #[Regex("/(?!^(\d+|[a-zA-Z]+|[~!@#$%^&*()_.]+)$)^[\w~!@#$%^&*()_.]{8,26}$/", "登录密码应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串")]
    public function password(): bool
    {
        return true;
    }

}