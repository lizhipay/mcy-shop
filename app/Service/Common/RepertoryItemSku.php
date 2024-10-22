<?php
declare(strict_types=1);

namespace App\Service\Common;

use App\Const\MarketControl;
use App\Entity\Repertory\Sku;
use App\Model\User;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\RepertoryItemSku::class)]
interface RepertoryItemSku
{

    /**
     * @param int|\App\Model\RepertoryItemSku $skuModel
     * @param int|null $userId
     * @return Sku|null
     */
    public function getSKUEntity(int|\App\Model\RepertoryItemSku $skuModel, ?int $userId): ?Sku;


    /**
     * @param int|\App\Model\RepertoryItemSku $skuModel
     * @param int|User $userModel
     * @return bool
     */
    public function isDisplay(int|\App\Model\RepertoryItemSku $skuModel, int|User $userModel): bool;

    /**
     * @param string $price
     * @param int $repertoryItemSkuId
     * @param int $userId
     * @param int $type
     * @return void
     */
    public function marketControlCheck(string $price, int $repertoryItemSkuId, int $userId, int $type = MarketControl::TYPE_VISITOR): void;

    /**
     * @param User|null $user
     * @param int $skuId
     * @return array
     */
    public function getWholesale(?User $user, int $skuId): array;

    /**
     * @param int $repertoryItemSkuId
     * @param int $type
     * @param string $value
     * @return void
     */
    public function setCache(int $repertoryItemSkuId, int $type, string $value): void;


    /**
     * @param int $repertoryItemSkuId
     * @param int $type
     * @return string|null
     */
    public function getCache(int $repertoryItemSkuId, int $type): ?string;

    /**
     * @param int $repertoryItemSkuId
     * @param bool $force
     * @return void
     */
    public function delCache(int $repertoryItemSkuId, bool $force = false): void;


    /**
     * @param int $repertoryItemSkuId
     * @return void
     */
    public function syncCache(int $repertoryItemSkuId): void;


    /**
     * @param int $repertoryItemId
     * @return void
     */
    public function syncCacheForItem(int $repertoryItemId): void;


    /**
     * @param int $repertoryItemId
     * @return void
     */
    public function delCacheForItem(int $repertoryItemId): void;
}