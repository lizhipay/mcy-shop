<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Cart
{


    #[Required("购买数量不能为空")]
    #[Regex("/^(?!0)\d{1,11}$/", "购买数量必须大于0，且不能超过11位")]
    public function quantity(): bool
    {
        return true;
    }


    #[Required("SKU不能为空")]
    #[Regex("/^[1-9]\d*$/", "SKU错误")]
    public function skuId(): bool
    {
        return true;
    }


    #[Required("物品ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "物品ID错误")]
    public function itemId(): bool
    {
        return true;
    }
}