<?php
declare (strict_types=1);

namespace App\Validator;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Util\Verify;

class Common
{
    #[Required("名称不能为空")]
    public function name(): bool
    {
        return true;
    }

    #[Required("页码不能为空")]
    #[Regex("/^[1-9]\d*$/", "页码错误")]
    public function page(): bool
    {
        return true;
    }

    #[Required("分页数量不能为空")]
    #[Regex("/^[1-9]\d*$/", "分页数量错误")]
    public function limit(): bool
    {
        return true;
    }


    #[Required("请不要禁用cookie")]
    #[Regex("/^.{32}$/", "客户端出现错误，请刷新网页")]
    public function clientId(): bool
    {
        return true;
    }

    #[Required("ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "ID错误")]
    public function id(): bool
    {
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


    #[Required("手机号不能为空")]
    public function phone(string $value): bool|string
    {
        if (!Verify::isChinaMobile($value) && !Verify::isInternationalMobile($value)) {
            return "手机号格式错误";
        }
        return true;
    }


    #[Required("状态不能为空")]
    #[Regex("/^[0-9]\d*$/", "状态代码错误")]
    public function status(): bool
    {
        return true;
    }


    #[Required("列表不能为空")]
    public function list(mixed $value): bool|string
    {
        if (!is_array($value)) {
            return '列表类型错误';
        }

        if (count($value) == 0) {
            return '列表不能为空';
        }

        return true;
    }
}