<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Model\UserBankCard;
use App\Model\UserWithdraw;
use Kernel\Annotation\Inject;
use Kernel\Database\Db;
use Kernel\Exception\ServiceException;
use Kernel\Util\Date;
use Kernel\Util\Str;

class Withdraw implements \App\Service\User\Withdraw
{

    #[Inject]
    private \App\Service\User\Balance $balance;

    /**
     * @param int $userId
     * @param int $cardId
     * @param string $amount
     * @return void
     * @throws \Throwable
     */
    public function apply(int $userId, int $cardId, string $amount): void
    {
        if ($amount <= 0) {
            throw new ServiceException("金额错误");
        }

        $card = UserBankCard::with('bank')->find($cardId);

        if (!$card || $card->user_id != $userId) {
            throw new ServiceException("银行卡不存在");
        }

        if ($card->status != 1) {
            throw new ServiceException("银行卡状态异常");
        }

        if ($card->bank?->status != 1) {
            throw new ServiceException("当前银行暂停使用，请更换其他银行的银行卡");
        }

        Db::transaction(function () use ($userId, $cardId, $amount) {
            $tradeNo = Str::generateTradeNo();
            $this->balance->deduct($userId, $amount, \App\Const\Balance::TYPE_WITHDRAW, $tradeNo);
            $userWithdraw = new UserWithdraw();
            $userWithdraw->user_id = $userId;
            $userWithdraw->card_id = $cardId;
            $userWithdraw->amount = $amount;
            $userWithdraw->status = 0;
            $userWithdraw->create_time = Date::current();
            $userWithdraw->trade_no = $tradeNo;
            $userWithdraw->save();
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }

    /**
     * @param int $withdrawId
     * @param bool $lockCard
     * @param int $status
     * @param string $message
     * @return void
     * @throws \Throwable
     */
    public function processed(int $withdrawId, bool $lockCard, int $status, string $message): void
    {
        Db::transaction(function () use ($lockCard, $message, $status, $withdrawId) {
            /**
             * @var UserWithdraw $withdraw
             */
            $withdraw = UserWithdraw::query()->find($withdrawId);

            if (!$withdraw) {
                throw new ServiceException("提现记录不存在");
            }

            if ($withdraw->status != 0) {
                throw new ServiceException("提现记录已处理，无法再次处理");
            }

            $withdraw->status = $status;
            $withdraw->handle_message = $message;
            $withdraw->handle_time = Date::current();
            $withdraw->save();

            //封禁银行卡
            if ($status == 2) {
                if ($lockCard) {
                    UserBankCard::query()->where("id", $withdraw->card_id)->update(['status' => 0]);
                }

                //驳回钱款
                $this->balance->add(
                    userId: $withdraw->user_id,
                    amount: (string)$withdraw->amount,
                    type: \App\Const\Balance::TYPE_WITHDRAW_REJECT,
                    isWithdraw: true,
                    tradeNo: $withdraw->trade_no
                );
            }
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }
}