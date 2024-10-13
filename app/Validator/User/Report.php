<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Model\OrderItem;
use App\Model\User;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Util\Context;

class Report
{
    #[Required("请选择维权原因")]
    #[Regex("/^[0-9]\d*$/", "维权原因选择不正确")]
    public function type(): bool
    {
        return true;
    }



    #[Required("请选择维权方式")]
    #[Regex("/^[0-9]\d*$/", "维权方式选择不正确")]
    public function expect(): bool
    {
        return true;
    }


    #[Required("维权内容不能为空")]
    #[Regex("/^(\S\s*?){20,}$/", "维权内容不得低于20个字")]
    public function message(): bool
    {
        return true;
    }
}