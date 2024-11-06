<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Shop\Markup;
use App\Entity\Shop\QuantityRestriction;
use App\Model\ItemSku;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\User;
use Hyperf\Collection\Collection;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Item::class)]
interface Item
{
    /**
     * @param User|null $customer
     * @param int|null $categoryId
     * @param User|null $merchant
     * @param string|null $keywords
     * @param int|null $page
     * @param int|null $size
     * @return array
     */
    public function list(?User $customer, ?int $categoryId, ?User $merchant, ?string $keywords = null, ?int $page = null, ?int $size = null): array;

    /**
     * @param User|null $customer
     * @param int $itemId
     * @param User|null $user
     * @return \App\Entity\Shop\Item
     */
    public function getItem(?User $customer, int $itemId, ?User $user): \App\Entity\Shop\Item;

    /**
     * @param User|null $customer
     * @param \App\Model\Item $item
     * @param Collection $itemSku
     * @param bool $source
     * @return \App\Entity\Shop\Item|null
     */
    public function getItemEntity(?User $customer, \App\Model\Item $item, Collection $itemSku, bool $source = false): ?\App\Entity\Shop\Item;

    /**
     * @param int $categoryId
     * @param int $itemId
     * @param int|array $markupId
     * @param User|null $user
     * @param bool $available
     * @return void
     */
    public function loadRepertoryItem(int $categoryId, int $itemId, int|array $markupId, ?User $user = null, bool $available = false): void;

    /**
     * @param string $amount
     * @param string $percentage
     * @param int $keepDecimals
     * @return string
     */
    public function getPercentageAmount(string $amount, string $percentage, int $keepDecimals): string;

    /**
     * @param \App\Model\Item $item
     * @param RepertoryItem $repertoryItem
     * @return void
     */
    public function syncRepertoryItem(\App\Model\Item $item, RepertoryItem $repertoryItem): void;

    /**
     * @param int $itemId
     * @return void
     */
    public function syncRepertoryItems(int $itemId): void;


    /**
     * @param int $markupTemplateId
     * @return void
     */
    public function syncRepertoryItemForMarkupTemplate(int $markupTemplateId): void;


    /**
     * @param int|\App\Model\Item $item
     * @return Markup
     */
    public function getMarkup(int|\App\Model\Item $item): Markup;


    /**
     * @param int $skuId
     * @return ItemSku
     */
    public function getSku(int $skuId): ItemSku;


    /**
     * @param User|null $customer
     * @param int $skuId
     * @return array
     */
    public function getWholesale(?User $customer, int $skuId): array;

    /**
     * @param int|null $userId
     * @param ?RepertoryItemSku $itemSku
     * @return QuantityRestriction
     */
    public function getQuantityRestriction(?int $userId, ?RepertoryItemSku $itemSku): QuantityRestriction;
}