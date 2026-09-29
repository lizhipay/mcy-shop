<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Const\Balance as Bce;
use App\Model\UserBill;
use App\Service\User\Balance;
use Kernel\Annotation\Inject;
use Kernel\Log\Log;
use Kernel\Util\Date;

class Bill implements \App\Service\User\Bill
{

    #[Inject]
    private Balance $balance;

    /**
     * 全额退款时，按交易号对该笔的所有资金做双向反向记账，使各方回到发货前状态：
     * 冻结(未落账)→取消；已落账的加款→扣回、扣款→补回。任一步失败(如余额已被花光)将抛异常，
     * 使整笔退款事务回滚，绝不放行“退款不守恒凭空造币”。须在事务中调用。
     * @param string $tradeNo
     * @return void
     * @throws \Throwable
     */
    public function fullReverse(string $tradeNo): void
    {
        $bills = UserBill::query()->where("trade_no", $tradeNo)
            ->whereIn("status", [Bce::STATUS_DIRECT, Bce::STATUS_DELAYED])->get();

        /**
         * @var UserBill $bill
         */
        foreach ($bills as $bill) {
            if ((int)$bill->status === Bce::STATUS_DELAYED) {
                $this->balance->rollback($bill->id); //冻结未落账 → 取消
                continue;
            }
            //已落账：按方向反向（加款→扣回；扣款→补回）
            if ((int)$bill->action === Bce::ACTION_ADD) {
                $this->balance->deduct((int)$bill->user_id, (string)$bill->amount, Bce::TYPE_ORDER_REFUND, $tradeNo);
            } else {
                $this->balance->add((int)$bill->user_id, (string)$bill->amount, Bce::TYPE_ORDER_REFUND, false, Bce::STATUS_DIRECT, 0, $tradeNo);
            }
            $bill->status = Bce::STATUS_ROLLBACK;
            $bill->update_time = Date::current();
            $bill->save();
        }
    }

    /**
     * 该方法需要在事物中执行，否则不安全
     * @param string $tradeNo
     * @return void
     * @throws \Throwable
     */
    public function unfreeze(string $tradeNo): void
    {
        $bill = UserBill::query()->where("status", 1)->where("trade_no", $tradeNo)->get();
        /**
         * @var UserBill $item
         */
        foreach ($bill as $item) {
            try {
                $this->balance->unfreeze($item->id);
            } catch (\Throwable $e) {
                Log::inst()->error("解冻资金时出错：" . $e->getMessage());
            }
        }
    }

    /**
     * @param string $tradeNo
     * @return void
     */
    public function rollback(string $tradeNo): void
    {
        $bill = UserBill::query()->where("status", 1)->where("trade_no", $tradeNo)->get();

        /**
         * @var UserBill $item
         */
        foreach ($bill as $item) {
            $this->balance->rollback($item->id);
        }
    }
}