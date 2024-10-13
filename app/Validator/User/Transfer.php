<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Controller\User\Base;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Transfer extends Base
{
    #[Required("收款人不能为空")]
    public function payee(): bool
    {
        return true;
    }


    #[Required("转账金额不能为空")]
    #[Regex("/^[0-9]+(\.[0-9]{1,2})?$/", "转账金额错误")]
    public function amount(): bool
    {
        return true;
    }
}