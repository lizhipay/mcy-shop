<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Bill::class)]
interface Bill
{

    /**
     * 账单解冻
     * 该方法需要在事物中执行，否则不安全
     * @param string $tradeNo
     * @return void
     */
    public function unfreeze(string $tradeNo): void;


    /**
     * 账单回滚
     * @param string $tradeNo
     * @return void
     */
    public function rollback(string $tradeNo): void;
}