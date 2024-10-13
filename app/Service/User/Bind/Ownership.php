<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Model\ItemMarkupTemplate;
use App\Model\ItemSku;
use App\Model\ItemSkuWholesale;
use App\Model\OrderItem;
use App\Model\User;
use App\Model\UserLevel;
use Kernel\Exception\JSONException;
use App\Model\Item;

class Ownership implements \App\Service\User\Ownership
{

    /**
     * @param bool ...$state
     * @return void
     * @throws JSONException
     */
    public function throw(bool ...$state): void
    {
        foreach ($state as $sta) {
            if (!$sta) {
                throw new JSONException("权限不足");
            }
        }
    }

    /**
     * @param int $userId
     * @param int $skuId
     * @return bool
     */
    public function itemSku(int $userId, int $skuId): bool
    {
        return ItemSku::query()->where("user_id", $userId)->where("id", $skuId)->exists();
    }


    /**
     * @param int $userId
     * @param int $levelId
     * @return bool
     */
    public function level(int $userId, int $levelId): bool
    {
        return UserLevel::query()->where("user_id", $userId)->where("id", $levelId)->exists();
    }

    /**
     * @param int $userId
     * @param int $memberId
     * @return bool
     */
    public function ownMember(int $userId, int $memberId): bool
    {
        return User::query()->where("pid", $userId)->where("id", $memberId)->exists();
    }

    /**
     * @param int $userId
     * @param int $itemId
     * @return bool
     */
    public function item(int $userId, int $itemId): bool
    {
        return Item::query()->where("user_id", $userId)->where("id", $itemId)->exists();
    }

    /**
     * @param int $userId
     * @param int $wholesaleId
     * @return bool
     */
    public function wholesale(int $userId, int $wholesaleId): bool
    {
        return ItemSkuWholesale::query()->where("user_id", $userId)->where("id", $wholesaleId)->exists();
    }

    /**
     * @param int $userId
     * @param int $markupId
     * @return bool
     */
    public function markup(int $userId, int $markupId): bool
    {
        return ItemMarkupTemplate::query()->where("user_id", $userId)->where("id", $markupId)->exists();
    }

    /**
     * @param int $customerId
     * @param int $orderItemId
     * @return bool
     */
    public function orderItem(int $customerId, int $orderItemId): bool
    {
        return OrderItem::query()->leftJoin("order", "order_item.order_id", "=", "order.id")->where("order.customer_id", $customerId)->where("order_item.id", $orderItemId)->exists();
    }
}