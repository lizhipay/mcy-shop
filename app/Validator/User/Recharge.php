<?php
declare (strict_types=1);

namespace App\Validator\User;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Recharge
{
    #[Required("金额不能为空")]
    #[Regex("/^[0-9]+(\.[0-9]{1,2})?$/", "金额错误")]
    public function amount(): bool
    {
        return true;
    }
}