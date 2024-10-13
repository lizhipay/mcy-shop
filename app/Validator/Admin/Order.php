<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Order
{

    #[Required("订单ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "订单ID错误")]
    public function orderId(): bool
    {
        return true;
    }

}