<?php
declare (strict_types=1);

namespace App\Service\Common;

use App\Model\RepertoryOrder;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Ship::class)]
interface Ship
{

    /**
     * 可优化代码到缓存
     * @param int $repertoryItemSkuId
     * @param RepertoryOrder|null $order
     * @return \Kernel\Plugin\Handle\Ship|null
     */
    public function getShip(int $repertoryItemSkuId, ?RepertoryOrder $order = null): ?\Kernel\Plugin\Handle\Ship;

    /**
     * 获取库存
     * @param int $repertoryItemSkuId
     * @return string
     */
    public function stock(int $repertoryItemSkuId): string;


    /**
     * @param int $repertoryItemSkuId
     * @param array $map
     * @return bool
     */
    public function inspection(int $repertoryItemSkuId, array $map): bool;

    /**
     * 库存是否充足
     * @param int $repertoryItemSkuId
     * @param int $quantity
     * @return bool
     */
    public function hasEnoughStock(int $repertoryItemSkuId, int $quantity = 1): bool;
}