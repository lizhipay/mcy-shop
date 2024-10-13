<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;

class PayConfig
{
    #[Required("配置名称不能为空")]
    public function name(): bool
    {
        return true;
    }
}