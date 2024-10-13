<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Model\Bank;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class UserBankCard
{

    #[Required("请选择银行")]
    #[Regex("/^[1-9]\d*$/", "请选择正确的银行")]
    public function bankId(mixed $value): bool|string
    {
        if (!Bank::query()->where("id", $value)->exists()) {
            return "该银行不存在";
        }

        return true;
    }

    #[Required("请填写银行卡号")]
    public function cardNo(mixed $value): bool
    {
        return true;
    }
}