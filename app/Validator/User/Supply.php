<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Supply
{
    #[Required("模版ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "模版ID格式错误")]
    public function markupId(): bool
    {
        return true;
    }

    #[Required("分类ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "分类ID格式错误")]
    public function categoryId(): bool
    {
        return true;
    }
}