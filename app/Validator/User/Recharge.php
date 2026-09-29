<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Recharge
{
    #[Required("金额不能为空")]
    //必须为正数金额（排除 0 / 0.00），且整数部分不超过 8 位（适配 decimal(10,2)，避免超范围写入报错/边界异常）
    #[Regex("/^(?!0+(\.0{1,2})?$)(?=\d{1,8}(\.\d{1,2})?$)[0-9]+(\.[0-9]{1,2})?$/", "金额错误")]
    public function amount(): bool
    {
        return true;
    }
}