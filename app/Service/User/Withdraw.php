<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Withdraw::class)]
interface Withdraw
{
    /**
     * @param int $userId
     * @param int $cardId
     * @param string $amount
     * @return void
     */
    public function apply(int $userId, int $cardId, string $amount): void;


    /**
     * @param int $withdrawId
     * @param bool $lockCard
     * @param int $status
     * @param string $message
     * @return void
     */
    public function processed(int $withdrawId, bool $lockCard, int $status, string $message): void;
}