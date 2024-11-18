<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Shop\CreateOrder;
use App\Entity\Shop\Product;
use App\Entity\Shop\Sku;
use App\Entity\Shop\Trade;
use App\Model\ItemSku;
use App\Model\ItemSkuLevel;
use App\Model\ItemSkuUser;
use App\Model\ItemSkuWholesale;
use App\Model\ItemSkuWholesaleLevel;
use App\Model\ItemSkuWholesaleUser;
use App\Model\OrderItem;
use App\Model\OrderRecharge;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\UserLevel;
use App\Service\Common\RepertoryOrder;
use App\Service\Common\Ship;
use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Container\Di;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Exception\ServiceException;
use Kernel\Log\Log;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;
use Kernel\Util\Decimal;
use Kernel\Util\Str;
use Kernel\Util\UserAgent;
use Kernel\Waf\Firewall;

class Order implements \App\Service\User\Order
{

    #[Inject]
    private Ship $ship;

    #[Inject]
    private RepertoryOrder $repertoryOrder;

    #[Inject]
    private \App\Service\User\Pay $pay;

    #[Inject]
    private \App\Service\User\Bill $bill;

    #[Inject]
    private \App\Service\User\Balance $balance;


    #[Inject]
    private \App\Service\User\Lifetime $lifetime;

    #[Inject]
    private \App\Service\User\OpenMerchant $openMerchant;


    #[Inject]
    private \App\Service\User\Level $level;


    #[Inject]
    private \App\Service\Common\RepertoryItemSku $repertoryItemSku;

    /**
     * 创建主订单
     * @param CreateOrder $createOrder
     * @param callable|null $callable $callable
     * @return mixed
     */
    public function create(CreateOrder $createOrder, ?callable $callable = null): mixed
    {
        $order = new \App\Model\Order();
        $order->trade_no = Str::generateTradeNo();
        $order->client_id = $createOrder->clientId;
        $order->create_ip = $createOrder->clientIp;
        $order->create_device = UserAgent::getDevice($createOrder->userAgent);
        $order->create_browser = UserAgent::getBrowser($createOrder->userAgent);
        $order->status = 0;
        $order->type = $createOrder->type;
        $order->create_time = Date::current();
        $order->total_amount = $createOrder->amount;
        $createOrder->customer && ($order->customer_id = $createOrder->customer->id); //客户
        $createOrder->merchant && ($order->user_id = $createOrder->merchant->id); //商家
        $createOrder->invite && ($order->invite_id = $createOrder->invite->id); //邀请人 or 推广人
        $createOrder->option && ($order->option = json_encode($createOrder->option));

        $order->save();

        if ($callable) {
            return call_user_func_array($callable, [&$order]);
        }

        return $order;
    }

    /**
     * @param array $items
     * @param string $clientId
     * @param string $createIp
     * @param string $createUa
     * @param User|null $customer
     * @param User|null $user
     * @param User|null $invite
     * @return Trade
     * @throws JSONException
     * @throws \Throwable
     */
    public function trade(array $items, string $clientId, string $createIp, string $createUa, ?User $customer = null, ?User $user = null, ?User $invite = null): Trade
    {
        /**
         * @var \App\Service\User\Item $itemService
         */
        $itemService = Di::inst()->make(\App\Service\User\Item::class);

        if (count($items) == 0) {
            throw new JSONException("您还没有选择商品");
        }

        if ($exists = \App\Model\Order::query()->where("status", 0)->where("create_time", ">", \date("Y-m-d H:i:s", time() - 360))->where("client_id", $clientId)->first()) {
            throw new JSONException($exists->trade_no, 9); //订单创建频繁
        }


        Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($user?->id), Point::SERVICE_ORDER_TRADE_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $items, $customer, $user, $invite);
        return Db::transaction(function () use ($createIp, $createUa, $clientId, $items, $customer, $user, $invite, $itemService) {
            $createOrder = new CreateOrder(\App\Const\Order::ORDER_TYPE_PRODUCT, $clientId, $createUa, $createIp);
            $createOrder->setMerchant($user);
            $createOrder->setCustomer($customer);
            $createOrder->setInvite($invite);
            $order = $this->create($createOrder, function (\App\Model\Order $order) use ($invite, $user, $customer, $items, $itemService, $clientId) {
                //创建商品
                foreach ($items as $cart) {

                    $skuId = (int)$cart['sku_id']; //SKU ID
                    $quantity = (int)$cart['quantity'];  //数量
                    //$contact = (string)$cart['contact'];  //联系方式
                    $widget = [];

                    if ($skuId <= 0) {
                        throw new JSONException("SKU错误");
                    }

                    /*         if (!preg_match("/^.{6,}$/", $contact)) {
                                 throw new JSONException("联系方式最低不少于6个字符");
                             }*/

                    if (!preg_match("/^(?!0)\d{1,11}$/", (string)$quantity)) {
                        throw new JSONException("购买数量必须大于0，且不能超过11位");
                    }

                    /**
                     * SKU对象
                     * @var ItemSku $itemSku
                     */
                    $itemSku = ItemSku::with(["repertoryItemSku", "item" => function (Relation $relation) {
                        $relation->with(["repertoryItem"]);
                    }])->find($skuId);

                    if (!$itemSku) {
                        throw new JSONException("SKU不存在");
                    }

                    /**
                     * 商品对象
                     * @var \App\Model\Item $item
                     */
                    $item = $itemSku->item;

                    # 判断商品是否上架
                    if ($item->status != 1) {
                        throw new JSONException(sprintf("[%s]商品还未上架", $item->name));
                    }

                    /**
                     * 仓库的商品对象
                     * @var RepertoryItem $repertoryItem
                     */
                    $repertoryItem = $item->repertoryItem;

                    if ($repertoryItem->status != 2) {
                        throw new JSONException(sprintf("[%s]商品源暂不可用", $item->name));
                    }

                    Plugin::instance()->unsafeMultiHook([$user?->id, $repertoryItem?->user_id], Point::SERVICE_ORDER_TRADE_CREATE_ITEM_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $cart, $order, $customer, $user, $invite);

                    $quantityRestriction = $itemService->getQuantityRestriction($user?->id, $itemSku->repertoryItemSku);
                    if ($quantityRestriction->min > $quantity) {
                        throw new JSONException(sprintf("[%s]购买数量不能低于 %d 件", $item->name, $quantityRestriction->min));
                    }

                    if ($quantityRestriction->max < $quantity && $quantityRestriction->total != 0) {
                        throw new JSONException(sprintf("[%s]购买数量不能大于 %d 件", $item->name, $quantityRestriction->max));
                    }

                    if ($quantityRestriction->total != 0) {
                        $totalPurchases = OrderItem::query()
                            ->leftJoin("order", "order_item.order_id", "=", "order.id")
                            ->where("order_item.sku_id", $skuId)
                            ->where("order.status", 1);

                        if ($customer) {
                            $totalPurchases = $totalPurchases->where("order.customer_id", $customer->id);
                        } else {
                            $totalPurchases = $totalPurchases->where("order.client_id", $clientId);
                        }

                        $totalPurchases = $totalPurchases->sum("quantity");

                        if (($totalPurchases + $quantity) > $quantityRestriction->total) {
                            throw new JSONException(sprintf("[%s]该商品最多购买 %d 件，您暂时已经无法进行购买", $item->name, $quantityRestriction->total));
                        }
                    }

                    //删除库存缓存
                    $this->repertoryItemSku->delCache((int)$itemSku->repertoryItemSku->id, true);
                    //检查库存
                    if (!$this->ship->hasEnoughStock((int)$itemSku->repertoryItemSku->id, $quantity)) {
                        throw new JSONException(sprintf("[%s(%s)]商品库存不足", $repertoryItem->name, $itemSku->name));
                    }

                    //widget处理
                    $widgets = (array)json_decode((string)$repertoryItem->widget, true) ?: [];

                    foreach ($widgets as $wid) {
                        if (!empty($wid['regex'])) {
                            if (!isset($cart[$wid['name']]) || $cart[$wid['name']] === "") {
                                throw new JSONException(sprintf("%s 不能为空", $wid['title']));
                            }
                            if (!preg_match("#{$wid['regex']}#", $cart[$wid['name']])) {
                                throw new JSONException((string)$wid['error']);
                            }
                        }
                        $widget[$wid['name']] = [
                            "title" => $wid['title'],
                            'value' => $cart[$wid['name']] ?? ""
                        ];
                    }

                    //海关检查
                    if (!$this->ship->inspection((int)$itemSku->repertoryItemSku->id, $cart)) {
                        throw new ServiceException("此商品暂时无法购买，请稍后再试");
                    }

                    //获取单价
                    $amount = $this->getAmount($customer, $itemSku, $quantity);
                    //获取分红金额
                    $dividendAmount = $this->getDividendAmount($invite, $itemSku, $quantity);

                    $totalAmount = (new Decimal($amount, 6))->mul((string)$quantity)->getAmount(2);

                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->item_id = $item->id;
                    $orderItem->sku_id = $itemSku->id;
                    $orderItem->quantity = $quantity;
                    $orderItem->amount = $totalAmount;
                    $invite && ($orderItem->dividend_amount = $dividendAmount);
                    $orderItem->status = 0;
                    //$orderItem->contact = $contact;
                    $orderItem->widget = $widget;
                    $orderItem->create_time = $order->create_time;
                    $orderItem->trade_no = Str::generateTradeNo();
                    $user && ($orderItem->user_id = $user->id);

                    Plugin::instance()->unsafeMultiHook([$user?->id, $repertoryItem?->user_id], Point::SERVICE_ORDER_TRADE_CREATE_ITEM_READY, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $cart, $order, $orderItem, $customer, $user, $invite);
                    $orderItem->save();
                    Plugin::instance()->unsafeMultiHook([$user?->id, $repertoryItem?->user_id], Point::SERVICE_ORDER_TRADE_CREATE_ITEM_FINISH, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $cart, $order, $orderItem, $customer, $user, $invite);
                    //增加总订单金额
                    $order->total_amount = (new Decimal((string)$order->total_amount, 2))->add($orderItem->amount)->getAmount();
                }
                $order->save();

                Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($user?->id), Point::SERVICE_ORDER_TRADE_CREATE_ORDER, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $order, $items, $customer, $user, $invite);
                return $order;
            });
            $trade = new Trade(
                $order->trade_no,
                (string)$order->total_amount,
                $order->create_time
            );
            Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($user?->id), Point::SERVICE_ORDER_TRADE_AFTER, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $trade, $items, $customer, $user, $invite);
            return $trade;
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }


    /**
     * @param string $amount
     * @param string $clientId
     * @param string $createIp
     * @param string $createUa
     * @param User|null $customer
     * @return Trade
     * @throws \Throwable
     */
    public function recharge(string $amount, string $clientId, string $createIp, string $createUa, ?User $customer = null): Trade
    {
        return Db::transaction(function () use ($amount, $customer, $createIp, $createUa, $clientId) {
            $createOrder = new CreateOrder(\App\Const\Order::ORDER_TYPE_RECHARGE, $clientId, $createUa, $createIp);

            if ($customer->pid > 0) {
                $createOrder->setMerchant(User::query()->find($customer->pid));
            }

            $createOrder->setCustomer($customer);
            $createOrder->setAmount($amount);
            $createOrder->setProductInfo("/assets/common/images/wallet.png", "余额");

            $order = $this->create($createOrder, function (\App\Model\Order $order) use ($customer, $amount) {
                $orderRecharge = new OrderRecharge();
                $customer->pid > 0 && ($orderRecharge->user_id = $customer->pid);
                $orderRecharge->order_id = $order->id;
                $orderRecharge->trade_no = Str::generateTradeNo();
                $orderRecharge->amount = $amount;
                $orderRecharge->status = 0;
                $orderRecharge->create_time = $order->create_time;
                $orderRecharge->save();
                return $order;
            });

            return new Trade(
                $order->trade_no,
                (string)$order->total_amount,
                $order->create_time
            );
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }


    /**
     * @param string $tradeNo
     * @return bool
     * @throws \Exception
     */
    public
    function cancel(string $tradeNo): bool
    {
        $order = \App\Model\Order::query()->where("trade_no", $tradeNo)->first();
        if (!$order) {
            return false;
        }
        if ($order->status != 0) {
            return false;
        }
        //$order->status = 2;
        $order->delete();
        return true;
    }

    /**
     * @param User|null $customer
     * @param ItemSku $itemSku
     * @param int $quantity
     * @return string
     */
    public
    function getAmount(?User $customer, ItemSku $itemSku, int $quantity = 1): string
    {
        //原始价格
        $prices[] = $itemSku->price;

        /**
         * @var UserLevel $level
         */
        $level = $customer?->level ?? null;


        /**
         * 查询批发价格
         * @var ItemSkuWholesale $itemSkuWholesale
         */
        $itemSkuWholesale = ItemSkuWholesale::query()->where("sku_id", $itemSku->id)->where("quantity", "<=", $quantity)->orderBy("quantity", "desc")->first();
        if ($itemSkuWholesale) {
            $prices[] = $itemSkuWholesale->price;
            if ($customer) {
                if ($level) {
                    /**
                     * 会员等级批发
                     * @var ItemSkuWholesaleLevel $itemSkuWholesaleLevel
                     */
                    $itemSkuWholesaleLevel = ItemSkuWholesaleLevel::query()->where("wholesale_id", $itemSkuWholesale->id)->where("level_id", $level->id)->where("status", 1)->first();
                    if ($itemSkuWholesaleLevel) {
                        $prices[] = $itemSkuWholesaleLevel->price;
                    }
                }

                /**
                 * 蜜价批发
                 * @var ItemSkuWholesaleUser $itemSkuWholesaleUser
                 */
                $itemSkuWholesaleUser = ItemSkuWholesaleUser::query()->where("customer_id", $customer->id)->where("wholesale_id", $itemSkuWholesale->id)->where("status", 1)->first();
                if ($itemSkuWholesaleUser) {
                    $prices[] = $itemSkuWholesaleUser->price;
                }
            }
        }

        if ($customer) {
            /**
             * 用户组价格
             * @var ItemSkuLevel $itemSkuLevel
             */
            if ($level) {
                $itemSkuLevel = ItemSkuLevel::query()->where("level_id", $level->id)->where("sku_id", $itemSku->id)->where("status", 1)->first();
                if ($itemSkuLevel) {
                    $prices[] = $itemSkuLevel->price;
                }
            }

            /**
             * 密价
             * @var ItemSkuUser $itemSkuUser
             */
            $itemSkuUser = ItemSkuUser::query()->where("customer_id", $customer->id)->where("sku_id", $itemSku->id)->where("status", 1)->first();
            if ($itemSkuUser) {
                $prices[] = $itemSkuUser->price;
            }
        }

        sort($prices);
        return (string)array_shift($prices);
    }


    /**
     * @param User|null $invite
     * @param ItemSku $itemSku
     * @param int $quantity
     * @return string
     */
    public function getDividendAmount(?User $invite, ItemSku $itemSku, int $quantity = 1): string
    {
        //原始价格
        $prices[] = $itemSku->dividend_amount ?? "0";

        /**
         * @var UserLevel $level
         */
        $level = $invite->level ?? null;

        //用户为空，代表是游客
        if (!$invite) {
            return "0";
        }

        /**
         * 查询批发价格
         * @var ItemSkuWholesale $itemSkuWholesale
         */
        $itemSkuWholesale = ItemSkuWholesale::query()->where("sku_id", $itemSku->id)->where("quantity", "<=", $quantity)->orderBy("quantity", "desc")->first();
        if ($itemSkuWholesale) {
            $prices[] = $itemSkuWholesale->dividend_amount;

            if ($level) {
                /**
                 * 会员等级批发
                 * @var ItemSkuWholesaleLevel $itemSkuWholesaleLevel
                 */
                $itemSkuWholesaleLevel = ItemSkuWholesaleLevel::query()->where("wholesale_id", $itemSkuWholesale->id)->where("level_id", $level->id)->where("status", 1)->first();
                if ($itemSkuWholesaleLevel) {
                    $prices[] = $itemSkuWholesaleLevel->dividend_amount;
                }
            }

            /**
             * 蜜价批发
             * @var ItemSkuWholesaleUser $itemSkuWholesaleUser
             */
            $itemSkuWholesaleUser = ItemSkuWholesaleUser::query()->where("customer_id", $invite->id)->where("wholesale_id", $itemSkuWholesale->id)->where("status", 1)->first();
            if ($itemSkuWholesaleUser) {
                $prices[] = $itemSkuWholesaleUser->dividend_amount;
            }
        }


        /**
         * 用户组价格
         * @var ItemSkuLevel $itemSkuLevel
         */
        if ($level) {
            $itemSkuLevel = ItemSkuLevel::query()->where("level_id", $level->id)->where("sku_id", $itemSku->id)->where("status", 1)->first();
            if ($itemSkuLevel) {
                $prices[] = $itemSkuLevel->dividend_amount;
            }
        }

        /**
         * 密价
         * @var ItemSkuUser $itemSkuUser
         */
        $itemSkuUser = ItemSkuUser::query()->where("customer_id", $invite->id)->where("sku_id", $itemSku->id)->where("status", 1)->first();
        if ($itemSkuUser) {
            $prices[] = $itemSkuUser->dividend_amount;
        }

        sort($prices);
        return (string)array_shift($prices);
    }

    /**
     * 订单被支付
     * @param User|null $customer
     * @param User|null $merchant
     * @param \App\Model\PayOrder $payOrder
     * @param \App\Model\Order $order
     * @param array $option
     * @return void
     */
    private function orderPaid(?User $customer, ?User $merchant, \App\Model\PayOrder $payOrder, \App\Model\Order $order, array $option): void
    {
        //消费
        if (in_array($order->type,
            [
                \App\Const\Order::ORDER_TYPE_PRODUCT,
                \App\Const\Order::ORDER_TYPE_UPGRADE_GROUP,
                \App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL
            ]
        )) {
            $customer && $this->lifetime->increment($customer->id, "total_consumption_amount", (string)$order->total_amount);
        }

        //充值
        if ($order->type == \App\Const\Order::ORDER_TYPE_RECHARGE) {
            $customer && $this->lifetime->increment($customer->id, "total_recharge_amount", (string)$order->total_amount);
        }
    }

    /**
     * @param \App\Model\Order $order
     * @param string $clientIp
     * @return void
     * @throws JSONException
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    public
    function deliver(\App\Model\Order $order, string $clientIp): void
    {

        if ($order->status != 3) {
            throw new JSONException("该商品无法使用该操作");
        }

        $now = Date::current();

        /**
         * @var \App\Model\PayOrder $payOrder
         */
        $payOrder = Di::inst()->make(\App\Service\User\PayOrder::class)->findPayOrder($order->id);
        $payOwner = $this->pay->findPayOwner($payOrder->pay_id);


        if ($order->type != \App\Const\Order::ORDER_TYPE_PRODUCT && $order->type != \App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL && $payOwner !== \App\Service\User\Pay::OWNER_OFFICIAL && $payOwner !== null) {
            throw new JSONException("该业务无法使用自定义支付接口");
        }

        //如果使用组合支付，需要扣除用户余额
        if ($payOrder->trade_amount > 0 && $payOrder->balance_amount > 0) {
            try {
                $this->balance->deduct($payOrder->customer_id, (string)$payOrder->balance_amount, \App\Const\Balance::TYPE_SHOPPING, $order->trade_no);
            } catch (\Throwable $e) {
                //如果抛异常，则退还在线支付金额到余额
                $this->balance->add(
                    userId: $payOrder->customer_id,
                    amount: (string)$payOrder->trade_amount,
                    type: \App\Const\Balance::TYPE_ORDER_REFUND,
                    isWithdraw: false,
                    tradeNo: $order->trade_no
                );
                $order->pay_time = $now;
                $order->status = 1;
                $order->save();
                return;
            }
        }

        //订单参数
        $option = (array)json_decode((string)$order->option, true);

        switch ($order->type) {
            case \App\Const\Order::ORDER_TYPE_PRODUCT:
                $items = OrderItem::with(["sku", "item" => function (HasOne $hasOne) {
                    $hasOne->with("repertoryItem");
                }])->where("order_id", $order->id)->get();

                //如果是商家自己的支付接口，且有在线支付金额，需要直接扣款，收多少钱，扣多少
                if ($payOwner === \App\Service\User\Pay::OWNER_MERCHANT && $payOrder->trade_amount > 0) {
                    $this->balance->deduct($order->user_id, $payOrder->trade_amount, \App\Const\Balance::TYPE_DEPOSIT, $order->trade_no);
                    Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($order->user_id), Point::SERVICE_ORDER_DELIVER_PRODUCT_PAY_OWNER_MERCHANT, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $order, $payOrder);
                }

                /**
                 * @var OrderItem $item
                 */
                foreach ($items as $item) {
                    /**
                     * @var ItemSku $sku
                     */
                    $sku = $item->sku;
                    try {
                        /**
                         * @var RepertoryItem $repertoryItem
                         */
                        $repertoryItem = $item->item->repertoryItem;
                        $balanceStatus = $repertoryItem->refund_mode == \App\Const\RepertoryItem::REFUND_MODE_UNCONDITIONALLY ? ($repertoryItem->auto_receipt_time == 0 ? \App\Const\Balance::STATUS_DIRECT : \App\Const\Balance::STATUS_DELAYED) : \App\Const\Balance::STATUS_DIRECT;
                        $balanceFreeze = (int)$repertoryItem->auto_receipt_time * 60;
                        //获取进货价
                        $stockAmount = $this->repertoryOrder->getAmount($order->user, $sku->repertoryItemSku, $item->quantity);
                        $itemAmount = $item->amount;

                        if ($order->user_id > 0) {
                            //如果此订单是延迟到账订单，则需要拨款给商家，否则商家没有足够的金额进货
                            if ($balanceStatus === \App\Const\Balance::STATUS_DELAYED) {
                                $this->balance->add(
                                    userId: $order->user_id,
                                    amount: $stockAmount,
                                    type: \App\Const\Balance::TYPE_APPROPRIATION,
                                    isWithdraw: false,
                                    tradeNo: $item->trade_no
                                );
                                $itemAmount = (new Decimal($item->amount))->sub($stockAmount)->getAmount();
                            }

                            //去掉分红，结算金额
                            $settlementAmount = (new Decimal($itemAmount))->sub((string)$item->dividend_amount)->getAmount();
                            if ($settlementAmount > 0) {
                                //商品出售加款
                                $this->balance->add(
                                    userId: $order->user_id,
                                    amount: $settlementAmount,
                                    type: \App\Const\Balance::TYPE_GOODS_SALE,
                                    isWithdraw: true,
                                    status: $balanceStatus,
                                    freeze: $balanceFreeze,
                                    tradeNo: $item->trade_no
                                );
                            } elseif ($settlementAmount < 0) {
                                $this->balance->deduct($order->user_id, -$settlementAmount, \App\Const\Balance::TYPE_GOODS_SALE, $item->trade_no);
                            }
                        }

                        $item->auto_receipt_time = \date("Y-m-d H:i:s", time() + ($repertoryItem->auto_receipt_time * 60));
                        $item->refund_mode = $repertoryItem->refund_mode;
                        $widget = [];
                        foreach ($item->widget as $name => $w) {
                            $widget[$name] = $w['value'];
                        }

                        try {
                            $trade = new \App\Entity\Repertory\Trade($order->user_id, $sku->repertory_item_sku_id, $item->quantity);
                            $trade->setMainTradeNo($order->trade_no);
                            $trade->setTradeNo($item->trade_no);
                            $trade->setWidget($widget);
                            $trade->setAmount((string)$item->amount);
                            $deliver = $this->repertoryOrder->trade($trade, $clientIp);
                            $item->treasure = $deliver->contents;
                            $item->status = $deliver->status == 0 ? 0 : ($repertoryItem->auto_receipt_time == 0 ? 3 : 1);
                        } catch (\Throwable $e) {
                            Log::inst()->error("发货失败：" . $e->getMessage());
                            $item->treasure = "上游发货失败，请自行补单";
                            $item->status = 6;
                        }
                        //给推广人分红
                        $this->dividend($order, $item, $balanceStatus, $balanceFreeze);

                        //发货成功
                        Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($order->user_id), Point::SERVICE_ORDER_DELIVER_PRODUCT_SUCCESS, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $item, $order);
                    } catch (\Throwable  $e) {
                        Log::inst()->debug("出现错误：{$e->getMessage()}");
                        $item->status = 2;
                        Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($order->user_id), Point::SERVICE_ORDER_DELIVER_PRODUCT_ERROR, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $item, $order);
                    }

                    $item->update_time = $now;
                    $item->save();
                }
                break;
            case \App\Const\Order::ORDER_TYPE_RECHARGE:
                /**
                 * @var OrderRecharge $orderRecharge
                 */
                $orderRecharge = OrderRecharge::query()->where("order_id", $order->id)->first();
                $orderRecharge->status = 1;
                $orderRecharge->recharge_time = $now;
                $orderRecharge->save();
                //给用户充值
                $this->balance->add(
                    userId: $order->customer_id,
                    amount: (string)$orderRecharge->amount,
                    type: \App\Const\Balance::TYPE_RECHARGE,
                    isWithdraw: false,
                    tradeNo: $order->trade_no
                );
                break;
            case \App\Const\Order::ORDER_TYPE_UPGRADE_GROUP:
                isset($option['group_id']) && $this->openMerchant->become($order->customer_id, (int)$option['group_id'], true, $order->trade_no);
                break;
            case \App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL:
                if (isset($option['level_id'])) {
                    $levelId = (int)$option['level_id'];
                    $level = UserLevel::find($levelId);
                    if ($level) {
                        /**
                         * @var User $merchant
                         */
                        $merchant = $level?->user;
                        /**
                         * @var UserGroup $group
                         */
                        $group = $merchant?->group;

                        if ($merchant && $group) {
                            $taxRatio = $group->tax_ratio > 0 ? (new Decimal($order->total_amount))->mul($group->tax_ratio)->getAmount() : 0; //费率
                            //自定义接口，并且收了钱
                            if ($payOwner === \App\Service\User\Pay::OWNER_MERCHANT) {
                                $taxRatio > 0 && $this->balance->deduct($merchant->id, $taxRatio, \App\Const\Balance::TYPE_ORDER_DIVIDEND, $order->trade_no);
                            } else if ($payOwner === \App\Service\User\Pay::OWNER_OFFICIAL || ($payOwner === \App\Service\User\Pay::OWNER_MERCHANT && $payOrder->trade_amount <= 0)) {
                                $settlementAmount = (new Decimal($order->total_amount))->sub($taxRatio)->getAmount();
                                if ($settlementAmount > 0) {
                                    $this->balance->add(
                                        userId: $merchant->id,
                                        amount: $settlementAmount,
                                        type: \App\Const\Balance::TYPE_ORDER_DIVIDEND,
                                        isWithdraw: true,
                                        tradeNo: $order->trade_no
                                    );
                                }
                            }
                        }
                        $this->level->upgrade($order->customer_id, $levelId);
                    }
                }
                break;
            case \App\Const\Order::ORDER_TYPE_PLUGIN:
                //TODO: 待实现，插件发起下单功能
                break;
            default:
                throw new JSONException("订单类型错误");
        }

        $order->pay_time = $now;
        $order->status = 1;
        $order->save();

        //订单被支付
        $this->orderPaid($order->customer, $order->user, $payOrder, $order, $option);
    }


    /**
     * @param string $tradeNo
     * @param string|null $treasure
     * @param int $status
     * @return void
     * @throws \ReflectionException
     */
    public function syncDeliver(string $tradeNo, ?string $treasure, int $status): void
    {
        /**
         * @var OrderItem $orderItem
         */
        $orderItem = OrderItem::query()->where("trade_no", $tradeNo)->first();
        if (!$orderItem) {
            return;
        }
        (($orderItem->status == 0 || $orderItem->status == 2) && $status == 1) && ($orderItem->status = 1);
        $treasure && $orderItem->treasure = Firewall::inst()->xssKiller($treasure);
        $orderItem->save();
    }


    /**
     * @param int $orderItemId
     * @return void
     * @throws \Throwable
     */
    public function itemRestock(int $orderItemId): void
    {
        Db::transaction(function () use ($orderItemId) {
            /**
             * @var OrderItem $orderItem
             */
            $orderItem = OrderItem::with(['order'])->find($orderItemId);
            /**
             * @var \App\Model\Order $order
             */
            $order = $orderItem->order;

            /**
             * @var ItemSku $sku
             */
            $sku = $orderItem->sku;

            /**
             * @var RepertoryItem $repertoryItem
             */
            $repertoryItem = $orderItem?->item?->repertoryItem;

            if (!$orderItem || !$order || !$sku || !$repertoryItem) {
                throw new JSONException("订单不存在");
            }

            if ($orderItem->status != 6) {
                throw new JSONException("订单状态错误");
            }

            $widget = [];
            foreach ($orderItem->widget as $name => $w) {
                $widget[$name] = $w['value'];
            }

            $trade = new \App\Entity\Repertory\Trade($order->user_id, $sku->repertory_item_sku_id, $orderItem->quantity);
            $trade->setMainTradeNo($order->trade_no);
            $trade->setTradeNo($orderItem->trade_no);
            $trade->setWidget($widget);
            $trade->setAmount($orderItem->amount);
            $deliver = $this->repertoryOrder->trade($trade, $order->create_ip);

            $orderItem->treasure = $deliver->contents;
            $orderItem->status = $repertoryItem->auto_receipt_time == 0 ? 3 : 1;
            $orderItem->update_time = Date::current();
            $orderItem->save();
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }

    /**
     * @param int $repertoryItemId
     * @return int
     */
    public
    function getItemSold(int $repertoryItemId): int
    {
        return (int)\App\Model\RepertoryOrder::query()->where("repertory_item_id", $repertoryItemId)->where("status", 1)->sum("quantity");
    }


    /**
     * @throws JSONException
     */
    public
    function getCheckoutOrder(string $tradeNo): \App\Entity\Shop\Order
    {
        /**
         * @var \App\Model\Order $order
         */
        $order = \App\Model\Order::query()->where("trade_no", $tradeNo)->first();
        if (!$order) {
            throw new JSONException("订单不存在");
        }


        $orderEntity = new \App\Entity\Shop\Order($order);
        $items = [];

        if ($order->type == \App\Const\Order::ORDER_TYPE_PRODUCT) {
            $orderItems = OrderItem::with(['item', 'sku'])->where("order_id", $order->id)->get();
            /**
             * @var OrderItem $orderItem
             */
            foreach ($orderItems as $orderItem) {
                $orderItemEntity = new \App\Entity\Shop\OrderItem($orderItem);
                $orderItemEntity->setItem(new \App\Entity\Shop\Item($orderItem->item));
                $orderItemEntity->setSku(new Sku($orderItem->sku));
                $items[] = $orderItemEntity;
            }
        }

        /**
         * @var \App\Model\PayOrder $payOrder
         */
        $payOrder = \App\Model\PayOrder::query()->where("order_id", $order->id)->first();
        $payOrder && $orderEntity->setPayOrder(new \App\Entity\Pay\Order($payOrder));

        $option = json_decode((string)$order->option, true);
        if (isset($option['product'])) {
            $product = $option['product'];
            $orderEntity->setProduct(new Product($product['icon'] ?? "", $product['name'] ?? "", $product['quantity'] ?? 1));
        }

        $orderEntity->setItems($items);
        return $orderEntity;
    }


    /**
     * @param User|null $customer
     * @param string $clientId
     * @param string|null $tradeNo
     * @return \App\Entity\Shop\Order|null
     */
    public
    function getOrder(?User $customer, string $clientId, ?string $tradeNo): ?\App\Entity\Shop\Order
    {
        $order = \App\Model\Order::with(['item'])->where("type", 0);

        if ($tradeNo) {
            $order = $order->where("trade_no", $tradeNo)->first();
        } elseif ($customer) {
            $order = $order->where("customer_id", $customer->id)->orderBy("id", "desc")->first();
        } else {
            $order = $order->where("client_id", $clientId)->orderBy("id", "desc")->first();
        }

        if (!$order) {
            return null;
        }

        //get payOrder
        $payOrder = \App\Model\PayOrder::with(['pay'])->where("order_id", $order->id)->first();


        $orderEntity = new \App\Entity\Shop\Order($order);
        $items = [];
        /**
         * @var OrderItem $item
         */
        foreach ($order->item as $item) {
            $orderItem = new \App\Entity\Shop\OrderItem($item);
            $item->sku && $orderItem->setSku(new Sku($item->sku));
            $item->item && $orderItem->setItem(new \App\Entity\Shop\Item($item->item));
            if (in_array($item->status, [1, 3, 4])) {
                $ship = $this->getOrderItemShip($item);
                if ($ship) {
                    $orderItem->setRender($ship->isCustomRender());
                }
            }
            $items[] = $orderItem;
        }

        $orderEntity->setItems($items);

        if ($payOrder) {
            $payOrderEntity = new \App\Entity\Pay\Order($payOrder);
            if ($payOrder->pay) {
                $payOrderEntity->setPay(new \App\Entity\Pay\Pay($payOrder->pay));
            }
            $orderEntity->setPayOrder($payOrderEntity);
        }

        return $orderEntity;
    }


    /**
     * @param int|OrderItem $idOrModel
     * @return \App\Entity\Shop\OrderItem|null
     */
    public function getOrderItem(int|OrderItem $idOrModel): ?\App\Entity\Shop\OrderItem
    {
        $item = $idOrModel;

        if (is_int($idOrModel)) {
            $item = OrderItem::query()->find($idOrModel);
        }

        if (!$item) {
            return null;
        }

        $orderItem = new \App\Entity\Shop\OrderItem($item);

        if ($item?->order?->status == 1 && $item->status != 5) {
            $ship = $this->getOrderItemShip($item);
            if ($ship) {
                $orderItem->setRender($ship->isCustomRender());
            }

            $orderItem->setTreasure($orderItem->render === false ? (string)$item->treasure : $ship->render());
            $orderItem->setWidget($item->widget);
        }
        $item->sku && $orderItem->setSku(new Sku($item->sku));
        $item->item && $orderItem->setItem(new \App\Entity\Shop\Item($item->item));
        $orderItem->setMessage($item?->sku?->repertoryItemSku?->message);
        return $orderItem;
    }

    /**
     * @param Collection $list
     * @return void
     * @throws \Throwable
     */
    private function autoReceiptItem(Collection $list): void
    {
        /**
         * @var OrderItem $orderItem
         */
        foreach ($list as $orderItem) {
            OrderItem::where("id", $orderItem->id)->update(['status' => 3, 'update_time' => Date::current()]);
            //更新关连账单
            try {
                Db::transaction(function () use ($orderItem) {
                    $this->bill->unfreeze($orderItem->trade_no);
                }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
            } catch (\Throwable $e) {
            }
        }
    }


    /**
     * @param int|null $userId
     * @return void
     * @throws \Throwable
     */
    public function autoReceipt(?int $userId = null): void
    {

        $query = OrderItem::query()->whereIn("order_item.status", [1, 2])->where("order_item.auto_receipt_time", "<=", Date::current());

        //主站
        $main = function () use ($query, $userId): Collection {
            return (clone $query)->get();
        };

        //供货商
        $supplier = function () use ($query, $userId): Collection {
            return (clone $query)->leftJoin("item", "order_item.item_id", "=", "item.id")
                ->leftJoin("repertory_item", "item.repertory_item_id", "=", "repertory_item.id")
                ->where("repertory_item.user_id", $userId)
                ->get("order_item.*");
        };

        //商家
        $merchant = function () use ($query, $userId): Collection {
            return (clone $query)->where("order_item.user_id", $userId)->get("order_item.*");
        };

        //客户
        $customer = function () use ($query, $userId): Collection {
            return (clone $query)->leftJoin("order", "order_item.order_id", "=", "order.id")
                ->where("order.customer_id", $userId)
                ->get("order_item.*");
        };

        $user = $userId > 0 ? User::query()->with("group")->find($userId) : null;

        if ($user) {
            //customer
            $this->autoReceiptItem($customer());
            if ($user->group) {
                //merchant
                if ($user->group->is_merchant) {
                    $this->autoReceiptItem($merchant());
                }
                //supplier
                if ($user->group->is_supplier) {
                    $this->autoReceiptItem($supplier());
                }
            }
        } else {
            $this->autoReceiptItem($main());
        }
    }

    /**
     * @param int $orderItemId
     * @return void
     * @throws \Throwable
     */
    public function receipt(int $orderItemId): void
    {
        Db::transaction(function () use ($orderItemId) {
            /**
             * @var OrderItem $orderItem
             */
            $orderItem = OrderItem::query()->find($orderItemId);
            if (!$orderItem) {
                throw new ServiceException("订单不存在");
            }

            if (!in_array($orderItem->status, [1, 2, 4])) {
                throw new ServiceException("该订单无法确认收货");
            }

            $orderItem->status = 3;
            $orderItem->update_time = Date::current();
            $orderItem->save();
            $this->bill->unfreeze($orderItem->trade_no);
            //撤诉
            \App\Model\OrderReport::query()->where("order_item_id", $orderItemId)->update(['status' => 3, "handle_type" => 4]);
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }

    /**
     * @param int|OrderItem $idOrModel
     * @return \Kernel\Plugin\Handle\Ship|null
     */
    public function getOrderItemShip(int|OrderItem $idOrModel): ?\Kernel\Plugin\Handle\Ship
    {
        $item = $idOrModel;

        if (is_int($idOrModel)) {
            $item = OrderItem::query()->find($idOrModel);
        }

        if (!$item) {
            return null;
        }

        /**
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = $item->sku?->repertoryItemSku;
        /**
         * @var \App\Model\RepertoryOrder $repertoryOrder
         */
        $repertoryOrder = \App\Model\RepertoryOrder::query()->where("item_trade_no", $item->trade_no)->first();

        if (!$repertoryItemSku || !$repertoryOrder) {
            return null;
        }

        return $this->ship->getShip($repertoryItemSku->id, $repertoryOrder);
    }

    /**
     * @param \App\Model\Order $order
     * @param OrderItem $orderItem
     * @param int $balanceStatus
     * @param int $balanceFreeze
     * @return void
     */
    public function dividend(\App\Model\Order $order, OrderItem $orderItem, int $balanceStatus, int $balanceFreeze): void
    {
        try {
            if ($order->invite_id <= 0 || $order->invite_id == null || $orderItem->dividend_amount <= 0 || $orderItem->dividend_amount == null) {
                return;
            }

            /*    if ($order->user_id > 0) {
                    $this->balance->deduct($order->user_id, (string)$orderItem->dividend_amount, \App\Const\Balance::TYPE_TRANSFER, $orderItem->trade_no);
                }*/

            $this->balance->add(
                userId: $order->invite_id,
                amount: (string)$orderItem->dividend_amount,
                type: \App\Const\Balance::TYPE_INVITE_DIVIDEND,
                isWithdraw: true,
                status: $balanceStatus,
                freeze: $balanceFreeze,
                tradeNo: $orderItem->trade_no
            );

            $this->lifetime->update($order->invite_id, "share_item_id", $orderItem->item_id); //更新最新推广的商品ID
            $this->lifetime->increment($order->invite_id, "share_item_count"); //更新推广商品数量
        } catch (\Throwable $e) {
            //Log::error("分红失败：{$e->getMessage()}");
        }
    }

    /**
     * @param string $ip
     * @param int $type
     * @param int $time
     * @param int $quantity
     * @param string $message
     * @return void
     * @throws JSONException
     */
    public function limiter(string $ip, int $type, int $time, int $quantity, string $message): void
    {
        $count = \App\Model\Order::query()
            ->where("create_ip", $ip)
            ->where("type", $type)
            ->where("create_time", ">", \date("Y-m-d H:i:s", time() - $time))
            ->count();

        if ($count >= $quantity) {
            throw new JSONException($message);
        }
    }

    /**
     * @param int $userId
     * @param int $type
     * @return void
     */
    public function clearUnpaidOrder(int $userId, int $type): void
    {
        \App\Model\Order::query()
            ->where("customer_id", $userId)
            ->where("type", $type)
            ->where("status", "!=", 1)
            ->delete();
    }
}