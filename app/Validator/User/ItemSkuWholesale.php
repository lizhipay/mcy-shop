<?php
declare (strict_types=1);

namespace App\Validator\User;

class ItemSkuWholesale
{

    public function price(mixed $value): bool|string
    {
        if ($value !== null && $value <= 0) {
            return '批发价，必须大于0';
        }

        return true;
    }
}