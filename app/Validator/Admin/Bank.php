<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;

class Bank
{
    #[Required("银行名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("银行代码不能为空", \Kernel\Validator\Required::LOOSE)]
    public function code(): bool
    {
        return true;
    }
}