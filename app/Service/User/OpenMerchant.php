<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Shop\Trade;
use App\Model\User;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\OpenMerchant::class)]
interface OpenMerchant
{

    /**
     * @param User $user
     * @param int $groupId
     * @param string $clientId
     * @param string $userAgent
     * @param string $clientIp
     * @return Trade
     */
    public function trade(User $user, int $groupId, string $clientId, string $userAgent, string $clientIp): Trade;


    /**
     * 成为商家
     * @param int $userId
     * @param int $groupId
     * @param bool $isDividend
     * @param string|null $tradeNo
     * @return bool
     */
    public function become(int $userId, int $groupId, bool $isDividend  = false, ?string $tradeNo = null): bool;
}