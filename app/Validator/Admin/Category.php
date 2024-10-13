<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;

class Category
{
    #[Required("分类名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }
}