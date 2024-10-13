<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;

class ItemSku
{
    #[Required("SKU名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    public function stockPrice(mixed $value): bool|string
    {
        if ($value !== null && $value <= 0) {
            return '进货价，必须大于0';
        }

        return true;
    }


    public function price(mixed $value): bool|string
    {
        if ($value !== null && $value <= 0) {
            return '零售价，必须大于0';
        }

        return true;
    }
}