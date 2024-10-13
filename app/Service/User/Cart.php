<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Shop\CartItem;
use App\Model\User;
use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\User\Bind\Cart::class)]
interface Cart
{

    /**
     * @param User|null $customer
     * @param string $clientId
     * @return array
     */
    public function getItems(?User $customer, string $clientId): array;

    /**
     * @param User|null $customer
     * @param string $clientId
     * @return string
     */
    public function getClientId(?User $customer, string $clientId): string;

    /**
     * 获取购物车总价
     * @param User|null $customer
     * @param string $clientId
     * @return string
     */
    public function getAmount(?User $customer, string $clientId): string;

    /**
     * 获取单个物品的信息
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @return CartItem
     */
    public function getItem(?User $customer, string $clientId, int $itemId): CartItem;

    /**
     * 添加物品到购物车
     * @param User|null $customer
     * @param string $clientId
     * @param int $quantity
     * @param int $skuId
     * @param array $option
     * @return bool
     */
    public function add(?User $customer, string $clientId, int $quantity, int $skuId, array $option): bool;


    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @param int $quantity
     * @return void
     */
    public function changeQuantity(?User $customer, string $clientId, int $itemId, int $quantity): void;

    /**
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @param array $option
     * @return void
     */
    public function updateOption(?User $customer, string $clientId, int $itemId, array $option): void;


    /**
     * 从购物车中移除某个物品
     * @param User|null $customer
     * @param string $clientId
     * @param int $itemId
     * @return bool
     */
    public function del(?User $customer, string $clientId, int $itemId): bool;


    /**
     * 清空购物车
     * @param User|null $customer
     * @param string $clientId
     * @return void
     */
    public function clear(?User $customer, string $clientId): void;


    /**
     * @param User $customer
     * @param string $clientId
     * @return void
     */
    public function bindUser(User $customer, string $clientId): void;
}