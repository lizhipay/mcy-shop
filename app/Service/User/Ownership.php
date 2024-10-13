<?php
declare (strict_types=1);

namespace App\Service\User;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Ownership::class)]
interface Ownership
{
    /**
     * @param int $userId
     * @param int $skuId
     * @return bool
     */
    public function itemSku(int $userId, int $skuId): bool;

    /**
     * @param int $userId
     * @param int $levelId
     * @return bool
     */
    public function level(int $userId, int $levelId): bool;

    /**
     * @param int $userId
     * @param int $memberId
     * @return bool
     */
    public function ownMember(int $userId, int $memberId): bool;


    /**
     * @param int $userId
     * @param int $itemId
     * @return bool
     */
    public function item(int $userId, int $itemId): bool;


    /**
     * @param int $userId
     * @param int $wholesaleId
     * @return bool
     */
    public function wholesale(int $userId, int $wholesaleId): bool;

    /**
     * @param int $userId
     * @param int $markupId
     * @return bool
     */
    public function markup(int $userId, int $markupId): bool;


    /**
     * @param int $customerId
     * @param int $orderItemId
     * @return mixed
     */
    public function orderItem(int $customerId, int $orderItemId): bool;

    /**
     * @param bool ...$state
     * @return void
     */
    public function throw(bool ...$state): void;
}