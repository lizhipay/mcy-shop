<?php
declare (strict_types=1);

namespace App\Validator\User;

class ItemSkuWholesale
{

    public function price(mixed $value): bool|string
    {
        if ($value === null || $value === "") {
            return true;
        }
        //必须为数值且大于0：杜绝 "abc" 等非数字绕过 <=0 比较、以及负数批发价污染计价
        if (!is_numeric($value) || (float)$value <= 0) {
            return '批发价，必须大于0';
        }

        return true;
    }
}