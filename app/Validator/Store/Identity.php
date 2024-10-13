<?php
declare (strict_types=1);

namespace App\Validator\Store;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Identity
{
    #[Required("姓名不能为空")]
    #[Regex("/^[\x{4e00}-\x{9fa5}]{2,}$/u", "姓名格式不正确")]
    public function certName(): bool
    {
        return true;
    }

    #[Required("身份证号码不能为空")]
    #[Regex("/^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(1[0-2]))(([0-2]\d)|3[0-1])\d{3}[\dXx]$/", "身份证号码不正确")]
    public function certNo(): bool
    {
        return true;
    }


    #[Required("单号不能为空", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^\d{24}$/", "单号错误")]
    public function tradeNo(): bool
    {
        return true;
    }
}