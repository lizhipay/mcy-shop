<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Order
{
    #[Required("订单号不能为空")]
    #[Regex("/^\d{24}$/", "订单号错误")]
    public function tradeNo(): bool
    {
        return true;
    }

    #[Required("物品ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "物品ID错误")]
    public function itemId(): bool
    {
        return true;
    }

    #[Required("订单ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "订单ID错误")]
    public function orderId(): bool
    {
        return true;
    }
}