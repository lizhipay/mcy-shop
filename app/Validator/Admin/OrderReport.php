<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class OrderReport
{
    #[Required("维权ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "维权ID错误")]
    public function reportId(): bool
    {
        return true;
    }

    #[Required("物品订单ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "物品订单ID错误")]
    public function itemId(): bool
    {
        return true;
    }

    #[Required("处理方式不能为空")]
    #[Regex("/^[0-3]$/", "不支持该处理方式")]
    public function handleType(): bool
    {
        return true;
    }

    #[Required("回复信息不能为空")]
    public function message(): bool
    {
        return true;
    }


    public function treasure(mixed $value, array $data): bool|string
    {
        if ($data['handle_type'] == 1 && ($value === null || $value === "")) {
            return "发货信息不能为空";
        }
        return true;
    }

    public function refundAmount(mixed $value, array $data): bool|string
    {
        if ($data['handle_type'] == 2 && ($value <= 0)) {
            return "退款金额必须大于0";
        }
        return true;
    }
}