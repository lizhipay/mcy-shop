<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Shop\CreateOrder;
use App\Entity\Shop\OrderItem;
use App\Entity\Shop\Trade;
use App\Model\ItemSku;
use App\Model\User;
use Kernel\Annotation\Bind;
use Kernel\Plugin\Handle\Ship;

#[Bind(class: \App\Service\User\Bind\Order::class)]
interface Order
{

    /**
     * 该方法建议在事物中执行
     * 创建主订单
     * @param CreateOrder $createOrder
     * @param callable|null $callable $callable
     * @return mixed
     */
    public function create(CreateOrder $createOrder, ?callable $callable = null): mixed;


    /**
     * 下单
     * @param array $items
     * @param string $clientId
     * @param string $createIp
     * @param string $createUa
     * @param User|null $customer
     * @param User|null $user
     * @param User|null $invite
     * @return Trade
     */
    public function trade(array $items, string $clientId, string $createIp, string $createUa, ?User $customer = null, ?User $user = null, ?User $invite = null): Trade;


    /**
     * @param string $amount
     * @param string $clientId
     * @param string $createIp
     * @param string $createUa
     * @param User|null $customer
     * @return Trade
     */
    public function recharge(string $amount, string $clientId, string $createIp, string $createUa, ?User $customer = null): Trade;

    /**
     * @param string $tradeNo
     * @return bool
     */
    public function cancel(string $tradeNo): bool;

    /**
     * @param User|null $customer
     * @param ItemSku $itemSku
     * @param int $quantity
     * @return string
     */
    public function getAmount(?User $customer, ItemSku $itemSku, int $quantity = 1): string;


    /**
     * @param User|null $invite
     * @param ItemSku $itemSku
     * @param int $quantity
     * @return string
     */
    public function getDividendAmount(?User $invite, ItemSku $itemSku, int $quantity = 1): string;


    /**
     * 完成订单：充值、商品订单、第三方插件发起订单
     * 该方法，必须保证100%可串行化事务
     * @param \App\Model\Order $order
     * @param string $clientIp
     * @return void
     */
    public function deliver(\App\Model\Order $order, string $clientIp): void;


    /**
     * @param string $tradeNo
     * @param string|null $treasure
     * @param int $status
     * @return void
     */
    public function syncDeliver(string $tradeNo, ?string $treasure, int $status): void;


    /**
     * @param int $orderItemId
     * @return void
     */
    public function itemRestock(int $orderItemId): void;

    /**
     * 获取商品出售次数
     * @param int $repertoryItemId
     * @return int
     */
    public function getItemSold(int $repertoryItemId): int;


    /**
     * 获取订单信息
     * @param string $tradeNo
     * @return \App\Entity\Shop\Order
     */
    public function getCheckoutOrder(string $tradeNo): \App\Entity\Shop\Order;

    /**
     * 获取订单
     * @param User|null $customer
     * @param string $clientId
     * @param string|null $tradeNo
     * @return \App\Entity\Shop\Order|null
     */
    public function getOrder(?User $customer, string $clientId, ?string $tradeNo): ?\App\Entity\Shop\Order;

    /**
     * @param int|\App\Model\OrderItem $idOrModel
     * @return OrderItem|null
     */
    public function getOrderItem(int|\App\Model\OrderItem $idOrModel): ?OrderItem;


    /**
     * 自动确认收货
     * @param int|null $userId
     * @return void
     */
    public function autoReceipt(?int $userId = null): void;


    /**
     * 确认收货
     * @param int $orderItemId
     * @return void
     */
    public function receipt(int $orderItemId): void;


    /**
     * 获取物品所在的发货插件
     * @param int|\App\Model\OrderItem $idOrModel
     * @return Ship|null
     */
    public function getOrderItemShip(int|\App\Model\OrderItem $idOrModel): ?\Kernel\Plugin\Handle\Ship;


    /**
     * 此方法必须在事物中执行
     * @param \App\Model\Order $order
     * @param \App\Model\OrderItem $orderItem
     * @param int $balanceStatus
     * @param int $balanceFreeze
     * @return void
     */
    public function dividend(\App\Model\Order $order, \App\Model\OrderItem $orderItem, int $balanceStatus, int $balanceFreeze): void;


    /**
     * 限流器
     * @param string $ip
     * @param int $type
     * @param int $time
     * @param int $quantity
     * @param string $message
     * @return void
     */
    public function limiter(string $ip, int $type, int $time, int $quantity, string $message): void;


    /**
     * 此方法请勿随意调用
     * @param int $userId
     * @param int $type
     * @return void
     */
    public function clearUnpaidOrder(int $userId, int $type): void;

}