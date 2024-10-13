<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;
use App\Const\Balance as Bce;

#[Bind(class: \App\Service\User\Bind\Balance::class)]
interface Balance
{

    /**
     * 增加资金
     * @param int $userId
     * @param string|float|int $amount
     * @param int $type
     * @param bool $isWithdraw
     * @param int $status
     * @param int $freeze
     * @param string|null $tradeNo
     * @param string|null $remark
     * @return int
     */
    public function add(int $userId, string|float|int $amount, int $type, bool $isWithdraw, int $status = Bce::STATUS_DIRECT, int $freeze = 0, ?string $tradeNo = null, ?string $remark = null): int;

    /**
     * 解冻资金
     * @param int $id
     * @return void
     */
    public function unfreeze(int $id): void;


    /**
     * 资金回滚
     * @param int $id
     * @return void
     */
    public function rollback(int $id): void;


    /**
     * 已到账的资金退款
     * @param int $id
     * @param bool $deductionWithdraw
     * @return bool
     */
    public function refund(int $id, bool $deductionWithdraw = false): bool;

    /**
     * 扣除资金
     * @param int $userId
     * @param string|float|int $amount
     * @param int $type
     * @param string|null $tradeNo
     * @param string|null $remark
     * @param bool $deductionWithdraw
     * @return void
     */
    public function deduct(int $userId, string|float|int $amount, int $type, ?string $tradeNo = null, ?string $remark = null, bool $deductionWithdraw = false): void;


    /**
     * 转账
     * @param int $payer
     * @param int $payee
     * @param string $amount
     * @return void
     */
    public function transfer(int $payer, int $payee, string $amount): void;
}