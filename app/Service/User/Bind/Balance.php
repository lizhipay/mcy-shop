<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Const\Balance as Bce;
use App\Model\User;
use App\Model\UserBill;
use Kernel\Annotation\Inject;
use Kernel\Exception\ServiceException;
use Kernel\Util\Date;
use Kernel\Util\Decimal;
use Kernel\Util\Str;

class Balance implements \App\Service\User\Balance
{

    #[Inject]
    private \App\Service\User\Lifetime $lifetime;


    /**
     * @param int $userId
     * @param string|float|int $amount
     * @param int $type
     * @param bool $isWithdraw
     * @param int $status
     * @param int $freeze
     * @param string|null $tradeNo
     * @param string|null $remark
     * @return int
     * @throws ServiceException
     */
    public function add(int $userId, string|float|int $amount, int $type, bool $isWithdraw, int $status = Bce::STATUS_DIRECT, int $freeze = 0, ?string $tradeNo = null, ?string $remark = null): int
    {
        if ($amount <= 0) {
            throw new ServiceException("金额错误");
        }

        $amount = (string)$amount;

        /**
         * @var User $user
         */
        $user = User::query()->lock()->find($userId);

        if (!$user) {
            throw new ServiceException("用户不存在");
        }

        $userBill = new UserBill();
        $userBill->user_id = $userId;
        $userBill->amount = $amount;
        $userBill->type = $type;
        $userBill->status = $status;
        $userBill->action = Bce::ACTION_ADD;
        $userBill->is_withdraw = (int)$isWithdraw;
        $remark && ($userBill->remark = $remark);
        $tradeNo && ($userBill->trade_no = $tradeNo);
        $userBill->create_time = Date::current();

        if ($status === Bce::STATUS_DIRECT) {
            $user->balance = $userBill->after_balance = (new Decimal((string)($userBill->before_balance = $user->balance), 2))->add($amount)->getAmount(2);
            //如果这笔资金可以提现，则增加提现额度
            $isWithdraw && ($user->withdraw_amount = (new Decimal((string)$user->withdraw_amount))->add($amount)->getAmount(2));
            $user->save();
        } else if ($status === Bce::STATUS_DELAYED) {
            $userBill->unfreeze_time = \date("Y-m-d H:i:s", time() + $freeze);
        } else {
            throw new ServiceException("暂不支持该状态");
        }

        $userBill->save();


        if (in_array($type, [
            Bce::TYPE_SUB_DIVIDEND,
            Bce::TYPE_ORDER_DIVIDEND,
            Bce::TYPE_INVITE_DIVIDEND
        ])) {
            try {
                $this->lifetime->increment($userId, "total_profit_amount", $amount);
            } catch (\Throwable $e) {
                \Kernel\Log\Log::inst()->error("增加用户总收益失败：{$e->getMessage()}");
            }
        }

        return $userBill->id;
    }

    /**
     * @param int $userId
     * @param string|float|int $amount
     * @param int $type
     * @param string|null $tradeNo
     * @param string|null $remark
     * @param bool $deductionWithdraw
     * @return void
     * @throws ServiceException
     */
    public function deduct(int $userId, string|float|int $amount, int $type, ?string $tradeNo = null, ?string $remark = null, bool $deductionWithdraw = false): void
    {
        if ($amount <= 0) {
            throw new ServiceException("金额错误");
        }

        $amount = (string)$amount;

        /**
         * @var User $user
         */
        $user = User::query()->lock()->find($userId);

        if (!$user) {
            throw new ServiceException("用户不存在");
        }

        $userBill = new UserBill();
        $userBill->user_id = $userId;
        $userBill->amount = $amount;
        $userBill->type = $type;
        $userBill->status = Bce::STATUS_DIRECT;
        $userBill->action = Bce::ACTION_DEDUCT;
        $userBill->is_withdraw = 0;
        $remark && ($userBill->remark = $remark);
        $tradeNo && ($userBill->trade_no = $tradeNo);
        $userBill->create_time = Date::current();
        $userBill->before_balance = $user->balance;
        $userBill->after_balance = (new Decimal((string)$userBill->before_balance, 2))->sub($amount)->getAmount();
        $user->balance = $userBill->after_balance;

        if ($user->balance < 0) {
            throw new ServiceException("余额不足");
        }

        if ($type === Bce::TYPE_WITHDRAW) {
            $user->withdraw_amount = (new Decimal((string)$user->withdraw_amount))->sub($amount)->getAmount();
            if ($user->withdraw_amount < 0) {
                throw new ServiceException("提现额度不足");
            }
        }

        if ($user->balance < $user->withdraw_amount) {
            $user->withdraw_amount = $user->balance;
        } elseif ($deductionWithdraw && $type != Bce::TYPE_WITHDRAW) {
            $user->withdraw_amount = (new Decimal((string)$user->withdraw_amount))->sub($amount)->getAmount();
        }

        $user->save();
        $userBill->save();
    }

    /**
     * @param int $id
     * @return void
     * @throws ServiceException
     */
    public function unfreeze(int $id): void
    {
        /**
         * @var UserBill $bill
         */
        $bill = UserBill::query()->lock()->find($id);
        if (!$bill) {
            throw new ServiceException("资金不存在");
        }

        if ($bill->status != 1) {
            throw new ServiceException("这笔资金无法解冻");
        }

        /**
         * @var User $user
         */
        $user = User::query()->lock()->find($bill->user_id);

        if (!$user) {
            throw new ServiceException("用户不存在");
        }

        $bill->status = Bce::STATUS_DIRECT;
        $bill->update_time = Date::current();
        $bill->before_balance = $user->balance;
        $bill->after_balance = (new Decimal((string)$bill->before_balance, 2))->add((string)$bill->amount)->getAmount(2);
        $user->balance = $bill->after_balance;
        $bill->is_withdraw == 1 && ($user->withdraw_amount = (new Decimal((string)$user->withdraw_amount))->add((string)$bill->amount)->getAmount(2));

        $user->save();
        $bill->save();
    }

    /**
     * @param int $id
     * @return void
     * @throws ServiceException
     */
    public function rollback(int $id): void
    {
        /**
         * @var UserBill $bill
         */
        $bill = UserBill::query()->lock()->find($id);
        if (!$bill) {
            throw new ServiceException("资金不存在");
        }

        if ($bill->status != 1) {
            throw new ServiceException("这笔资金无法回滚");
        }

        $bill->status = Bce::STATUS_ROLLBACK;
        $bill->update_time = Date::current();
        $bill->save();
    }


    /**
     * @param int $id
     * @param bool $deductionWithdraw
     * @return bool
     */
    public function refund(int $id, bool $deductionWithdraw = false): bool
    {
        if ($id <= 0) {
            return false;
        }

        /**
         * @var UserBill $bill
         */
        $bill = UserBill::query()->lock()->find($id);

        if (!$bill) {
            return false;
        }

        if ($bill->status != 0) {
            return false;
        }

        try {
            $this->deduct(
                userId: $bill->user_id,
                amount: (string)$bill->amount,
                type: \App\Const\Balance::TYPE_PAY_ORDER_REFUND,
                tradeNo: $bill->trade_no,
                deductionWithdraw: $deductionWithdraw
            );
        } catch (\Throwable $e) {
            return false;
        }

        $bill->status = Bce::STATUS_ROLLBACK;
        $bill->update_time = Date::current();
        $bill->save();

        return true;
    }

    /**
     * 转账
     * @param int $payer
     * @param int $payee
     * @param string $amount
     * @return void
     * @throws ServiceException
     */
    public function transfer(int $payer, int $payee, string $amount): void
    {
        $transferNo = Str::generateTradeNo(); //转账订单号
        //扣款
        $this->deduct($payer, $amount, Bce::TYPE_TRANSFER, $transferNo);
        //加款
        $this->add($payee, $amount, Bce::TYPE_TRANSFER, false, Bce::STATUS_DIRECT, 0, $transferNo);
    }
}