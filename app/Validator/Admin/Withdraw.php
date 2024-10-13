<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use App\Model\UserWithdraw;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Withdraw
{

    #[Required("ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "ID错误")]
    public function id(mixed $value): bool|string
    {
        $withdraw = UserWithdraw::query()->find($value);

        if (!$withdraw) {
            return '提现记录不存在';
        }

        if ($withdraw->status != 0) {
            return '提现记录已处理，无法再次处理';
        }

        return true;
    }


    #[Required("状态不能为空")]
    #[Regex("/^[0-9]\d*$/", "状态代码错误")]
    public function status(): bool
    {
        return true;
    }

    #[Required("锁定银行卡状态不能为空")]
    #[Regex("/^[0-9]\d*$/", "锁定银行卡状态代码错误")]
    public function lockCard(): bool
    {
        return true;
    }
}