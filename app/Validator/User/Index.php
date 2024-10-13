<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Index
{

    #[Required("商品ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "商品ID错误")]
    public function id(): bool
    {
        return true;
    }


    #[Required("订单号不能为空")]
    #[Regex("/^\d{24}$/", "订单号错误")]
    public function tradeNo(): bool
    {
        return true;
    }
}