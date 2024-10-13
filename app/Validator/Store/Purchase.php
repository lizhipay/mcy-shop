<?php
declare (strict_types=1);

namespace App\Validator\Store;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Purchase
{
    #[Required("类型不能为空")]
    #[Regex("/^[01]$/", "类型错误")]
    public function type(mixed $value): bool
    {
        return true;
    }

    #[Required("订阅类型错误")]
    #[Regex("/^[0-4]$/", "订阅类型错误")]
    public function subscription(mixed $value): bool
    {
        return true;
    }

    #[Required("ID不能为空")]
    public function itemId(mixed $value): bool
    {
        return true;
    }

    #[Required("请选择支付方式")]
    public function payId(mixed $value): bool
    {
        return true;
    }


    #[Required("金额不能为空")]
    #[Regex("/^\d+(\.\d{1,2})?$/", "金额格式错误")]
    public function amount(mixed $value): bool
    {
        return true;
    }
}