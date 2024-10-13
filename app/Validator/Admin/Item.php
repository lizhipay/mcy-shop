<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;


class Item
{
    #[Required("商品名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("商品说明不能为空", \Kernel\Validator\Required::LOOSE)]
    public function introduce(): bool
    {
        return true;
    }
}