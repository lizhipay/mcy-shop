<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class ItemSku
{
    #[Required("SKU名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    public function supplyPrice(mixed $value): bool|string
    {
        if ($value !== null && $value <= 0) {
            return '供货价，必须大于0';
        }

        return true;
    }
}