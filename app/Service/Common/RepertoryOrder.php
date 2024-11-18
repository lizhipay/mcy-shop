<?php
declare (strict_types=1);

namespace App\Service\Common;

use App\Entity\Repertory\Deliver;
use App\Entity\Repertory\Trade;
use App\Model\RepertoryItemSku;
use App\Model\User;
use Kernel\Annotation\Bind;
use Kernel\Plugin\Handle\Ship;

#[Bind(class: \App\Service\Common\Bind\RepertoryOrder::class)]
interface RepertoryOrder
{
    /**
     * @param Trade $trade
     * @param string $tradeIp
     * @param bool $direct
     * @return Deliver
     */
    public function trade(Trade $trade, string $tradeIp, bool $direct = false): Deliver;


    /**
     * @param User|null $customer
     * @param RepertoryItemSku $repertoryItemSku
     * @param int $quantity
     * @return string
     */
    public function getAmount(?User $customer, RepertoryItemSku $repertoryItemSku, int $quantity = 1): string;


    /**
     * 获取进货订单的插件
     * @param int|\App\Model\RepertoryOrder $order
     * @return Ship|null
     */
    public function getOrderShip(int|\App\Model\RepertoryOrder $order): ?Ship;
}