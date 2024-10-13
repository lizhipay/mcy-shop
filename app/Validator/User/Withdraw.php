<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Controller\User\Base;
use App\Model\UserBankCard;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Withdraw extends Base
{

    /**
     * @param mixed $value
     * @return bool|string
     */
    #[Required("请选择银行卡")]
    #[Regex("/^[1-9]\d*$/", "请选择正确的银行卡")]
    public function cardId(mixed $value): bool|string
    {

        $card = UserBankCard::with('bank')->find($value);

        if (!$card || $card->user_id != $this->getUser()->id) {
            return "银行卡不存在";
        }

        if ($card->status != 1) {
            return "银行卡状态异常";
        }

        if ($card->bank?->status != 1) {
            return "当前银行暂停使用，请更换其他银行的银行卡";
        }

        return true;
    }


    #[Required("提现金额不能为空")]
    #[Regex("/^[0-9]+(\.[0-9]{1,2})?$/", "提现金额错误")]
    public function amount(mixed $value): bool|string
    {
        if ($value > $this->getUser()->withdraw_amount) {
            return "提现额度不足";
        }
        return true;
    }

}