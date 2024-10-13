<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;
use Kernel\Validator\Required as RequiredAlias;

class Level
{
    #[Required("等级名称不能为空", RequiredAlias::LOOSE)]
    public function name(): bool
    {
        return true;
    }
}