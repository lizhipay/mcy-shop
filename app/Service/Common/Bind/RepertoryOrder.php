<?php
declare (strict_types=1);

namespace App\Service\Common\Bind;

use App\Const\Balance as Bce;
use App\Entity\Repertory\Deliver;
use App\Entity\Repertory\Trade;
use App\Model\OrderItem;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryItemSkuGroup;
use App\Model\RepertoryItemSkuUser;
use App\Model\RepertoryItemSkuWholesale;
use App\Model\RepertoryItemSkuWholesaleGroup;
use App\Model\RepertoryItemSkuWholesaleUser;
use App\Model\RepertoryOrder as Order;
use App\Model\RepertoryOrderCommission;
use App\Model\User;
use App\Model\UserGroup;
use App\Service\User\Balance;
use Kernel\Annotation\Inject;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\ServiceException;
use Kernel\Log\Log;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Handle\Ship;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;
use Kernel\Util\Decimal;
use Kernel\Util\Str;

class RepertoryOrder implements \App\Service\Common\RepertoryOrder
{

    #[Inject]
    private Balance $balance;
    #[Inject]
    private \App\Service\Common\Ship $ship;

    /**
     * @param Trade $trade
     * @param string $tradeIp
     * @param bool $direct
     * @return Deliver
     * @throws JSONException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function trade(Trade $trade, string $tradeIp, bool $direct = false): Deliver
    {
        if (strlen($trade->tradeNo) != 24) {
            throw new JSONException("订单号不能低于24位");
        }

        /**
         * @var User $customer
         */
        $customer = null; //系统

        if ($trade->customerId > 0) {
            $customer = User::query()->find($trade->customerId);
            if (!$customer) {
                throw new JSONException("用户不存在");
            }
        }

        /**
         * @var RepertoryItemSku $repertoryItemSku
         */
        $repertoryItemSku = RepertoryItemSku::with(["repertoryItem"])->find($trade->skuId);
        if (!$repertoryItemSku) {
            throw new JSONException("SKU 不存在");
        }


        /**
         * @var RepertoryItem $repertoryItem
         */
        $repertoryItem = $repertoryItemSku->repertoryItem;
        if (!$repertoryItem) {
            throw new JSONException("商品不存在");
        }

        if ($repertoryItem->status != 2) {
            throw new JSONException("商品未上架");
        }


        //处理控件
        $widget = [];

        //widget
        $widgetList = (array)json_decode((string)$repertoryItem->widget, true) ?: [];
        foreach ($widgetList as $item) {
            $value = $trade->widget[$item['name']] ?? "";
            if (!empty($item['regex'])) {
                if ($value === "") {
                    throw new JSONException(sprintf("%s 不能为空", $item['title']));
                }
                if (!preg_match("#{$item['regex']}#", $value)) {
                    throw new JSONException((string)$item['error']);
                }
            }

            $widget[$item['name']] = [
                "value" => $value,
                "title" => $item['title']
            ];
        }

        $widgetJson = json_encode($widget, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


        //获取单价，如果是用户，则获取用户的进货价，如果是系统，则获取供货商对系统设置的供货价格
        $amount = $this->getAmount($customer, $repertoryItemSku, $trade->quantity);
        $totalAmount = (new Decimal($amount, 6))->mul((string)$trade->quantity)->getAmount(2);

        Log::inst()->debug(sprintf("事物准备就绪，当前订单号：%s，订单金额：%s", $trade->tradeNo, $totalAmount));
        return Db::transaction(function () use ($direct, $customer, $widgetJson, $trade, $repertoryItemSku, $repertoryItem, $tradeIp, $totalAmount) {

            $repertoryOrder = new Order();
            $repertoryOrder->supply_profit = 0;
            $officeProfit = new Decimal($totalAmount); //官方获利
            $balanceStatus = $repertoryItem->refund_mode == \App\Const\RepertoryItem::REFUND_MODE_UNCONDITIONALLY ? ($repertoryItem->auto_receipt_time == 0 ? Bce::STATUS_DIRECT : Bce::STATUS_DELAYED) : Bce::STATUS_DIRECT;
            $balanceFreeze = (int)$repertoryItem->auto_receipt_time * 60;

            if ($customer && $repertoryItem->user_id != $trade->customerId) {
                //如果进货人是商家，供货的是其他供货商 或者 官方平台
                Log::inst()->debug(sprintf("订单类型：商家 -> %s", $repertoryItem->user_id > 0 ? "供货商({$repertoryItem->user_id})" : "平台直供"));
                //扣除进货人的余额
                $this->balance->deduct(userId: $trade->customerId, amount: $totalAmount, type: Bce::TYPE_RESTOCK, tradeNo: $trade->tradeNo);

                //返佣
                $this->calculateCommission($customer, $repertoryItemSku, $trade->quantity, $totalAmount, $trade->tradeNo, $balanceStatus, $balanceFreeze);
                $commission = RepertoryOrderCommission::query()->where("trade_no", $trade->tradeNo)->sum("amount");
                //出现分红，减去分红的钱
                $officeProfit = $officeProfit->sub((string)$commission);

                //供货商逻辑
                if ($repertoryItem->user_id > 0) {
                    $supplyPrice = (new Decimal($repertoryItemSku->supply_price, 6))->mul($trade->quantity)->getAmount();
                    //给供货商加钱
                    $this->balance->add(userId: $repertoryItem->user_id, amount: $supplyPrice, type: Bce::TYPE_SUPPLY_SETTLEMENT, isWithdraw: true, status: $balanceStatus, freeze: $balanceFreeze, tradeNo: $trade->tradeNo);
                    $repertoryOrder->supply_profit = $supplyPrice;
                    //减去分给供货商的钱
                    $officeProfit = $officeProfit->sub($supplyPrice);
                    Log::inst()->debug(sprintf("已经为供货商(%s)增加余额：%s，资金是否被冻结：%s，资金解冻时间：%s", $repertoryItem->user_id, $supplyPrice, $balanceStatus, $balanceFreeze));
                } else {
                    //平台直供
                    $officeProfit = $officeProfit->sub((new Decimal($repertoryItemSku->cost))->mul($trade->quantity)->getAmount());
                }

            } else if (!$customer && $repertoryItem->user_id > 0) {
                //如果进货人是平台，供货的是会员
                Log::inst()->debug(sprintf("订单类型：平台 -> %s", "供货商({$repertoryItem->user_id})"));
                $supplyPrice = (new Decimal((string)$repertoryItemSku->supply_price, 6))->mul((string)$trade->quantity)->getAmount();
                //给供货商加钱
                $this->balance->add(userId: $repertoryItem->user_id, amount: $supplyPrice, type: Bce::TYPE_SUPPLY_SETTLEMENT, isWithdraw: true, status: $balanceStatus, freeze: $balanceFreeze, tradeNo: $trade->tradeNo);
                //供货商盈利
                $repertoryOrder->supply_profit = $supplyPrice;
                //减去分给供货商的钱
                $orderItem = OrderItem::query()->where("trade_no", $trade->tradeNo)->first();
                if ($orderItem) {
                    $officeProfit = (new Decimal($orderItem->amount))->sub($supplyPrice);
                }
                Log::inst()->debug(sprintf("已经为供货商(%s)增加余额：%s，资金是否被冻结：%s，资金解冻时间：%s", $repertoryItem->user_id, $supplyPrice, $balanceStatus, $balanceFreeze));
            } elseif ($customer && $repertoryItem->user_id == $trade->customerId) {
                //如果进货人自己就是供货商
                Log::inst()->debug("订单类型：商家 -> 商家自己");
                //进货的逻辑
                if ($totalAmount > 0) {
                    $this->balance->deduct(userId: $trade->customerId, amount: $totalAmount, type: Bce::TYPE_RESTOCK, tradeNo: $trade->tradeNo);
                }
            } elseif (!$customer && !$repertoryItem->user_id) {
                //如果进货人是平台，供货的是平台
                Log::inst()->debug("订单类型：平台 -> 平台");
                $orderItem = OrderItem::query()->where("trade_no", $trade->tradeNo)->first();
                if ($orderItem) {
                    $officeProfit = (new Decimal($orderItem->amount))->sub((new Decimal($repertoryItemSku->cost))->mul($trade->quantity)->getAmount());
                }
            }

            $repertoryItem->user_id > 0 && ($repertoryOrder->user_id = $repertoryItem->user_id);
            $trade->customerId > 0 && ($repertoryOrder->customer_id = $trade->customerId);
            $repertoryOrder->trade_no = Str::generateTradeNo();
            $repertoryOrder->item_trade_no = $trade->tradeNo;
            $repertoryOrder->main_trade_no = $trade->mainTradeNo;
            $repertoryOrder->amount = $totalAmount;
            $repertoryOrder->repertory_item_id = $repertoryItem->id;
            $repertoryOrder->repertory_item_sku_id = $repertoryItemSku->id;
            $repertoryOrder->quantity = $trade->quantity;
            $repertoryOrder->trade_time = Date::current();
            $repertoryOrder->trade_ip = $tradeIp;
            $repertoryOrder->status = 0;
            $repertoryOrder->widget = $widgetJson;
            $repertoryOrder->item_cost = (new Decimal((string)$repertoryItemSku->cost, 6))->mul((string)$trade->quantity)->getAmount(); //成本
            //echo sprintf("订单金额：%s ，供货商成本：%s，返佣：%s\n", $totalAmount, $repertoryOrder->supply_profit, $commission);
            $repertoryOrder->office_profit = $officeProfit->getAmount();//官方获利
            Log::inst()->debug("官方获利：{$repertoryOrder->office_profit}");

            Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($repertoryItem->user_id), Point::SERVICE_REPERTORY_ORDER_TRADE_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $repertoryOrder, $trade, $customer, $widgetJson, $repertoryItemSku, $repertoryItem, $totalAmount);
            $repertoryOrder->save();
            //更新返佣表
            RepertoryOrderCommission::query()->where("trade_no", $trade->tradeNo)->update(["order_id" => $repertoryOrder->id]);

            //获取供货商的发货插件
            $env = Usr::instance()->userToEnv($repertoryItem->user_id);
            /**
             * 得到发货插件实例
             * @var \Kernel\Plugin\Abstract\Ship $ship
             */
            $ship = \Kernel\Plugin\Ship::instance()->getShipHandle($repertoryItem->plugin, $env, $repertoryItem, $repertoryItemSku, $repertoryOrder);

            if ($ship) {
                try {
                    //检查库存
                    if ($ship->hasEnoughStock()) {
                        $repertoryOrder->contents = $ship->delivery();
                    } else {
                        $repertoryOrder->contents = "库存不足，请申请售后";
                        $direct && throw new ServiceException("库存不足");
                    }
                } catch (\Throwable $e) {
                    $repertoryOrder->contents = "发货失败，请直接申请售后，失败原因：" . $e->getMessage();
                    $direct && throw new ServiceException($e->getMessage());
                }
            } else {
                $repertoryOrder->contents = "发货插件未启用，请申请售后";
                $direct && throw new ServiceException("发货插件未启用");
            }

            //发货
            $repertoryOrder->save();

            Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($repertoryItem->user_id), Point::SERVICE_REPERTORY_ORDER_TRADE_AFTER, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $repertoryOrder, $trade, $customer, $widgetJson, $repertoryItemSku, $repertoryItem, $totalAmount);
            return new Deliver($repertoryOrder);
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE);
    }

    /**
     * @param User $customer
     * @param RepertoryItemSku $repertoryItemSku
     * @param int $quantity
     * @param string $amount
     * @param string $tradeNo
     * @param int $balanceStatus
     * @param int $balanceFreeze
     * @return void
     * @throws JSONException
     * @throws \ReflectionException
     */
    private function calculateCommission(User $customer, RepertoryItemSku $repertoryItemSku, int $quantity, string $amount, string $tradeNo, int $balanceStatus, int $balanceFreeze): void
    {
        /**
         * @var User $parent
         */
        $parent = $customer->parent;
        if ($parent && $repertoryItemSku->user_id != $parent->id) { //如果上级就是这个商品的供货商，则不返佣
            //获得上级的拿货价格
            $parentAmount = (new Decimal($this->getAmount($parent, $repertoryItemSku, $quantity)))->mul((string)$quantity)->getAmount();
            $commission = (new Decimal($amount))->sub($parentAmount)->getAmount();
            Log::inst()->debug("层级返佣：检测到上级，返佣金额：{$commission}，上级拿货价：{$parentAmount}");
            if ($commission > 0) {
                try {
                    $this->balance->add(userId: $parent->id, amount: $commission, type: Bce::TYPE_SUB_DIVIDEND, isWithdraw: true, status: $balanceStatus, freeze: $balanceFreeze, tradeNo: $tradeNo);
                } catch (\Throwable $e) {
                    return;
                }
                //存储返佣记录
                $repertoryOrderCommission = new RepertoryOrderCommission(); //
                $repertoryOrderCommission->trade_no = $tradeNo;
                $repertoryOrderCommission->user_id = $customer->id;
                $repertoryOrderCommission->pid = $parent->id;
                $repertoryOrderCommission->amount = $commission;
                $repertoryOrderCommission->save();
                $superior = $parent->parent;
                if ($superior) {
                    $this->calculateCommission($parent, $repertoryItemSku, $quantity, $parentAmount, $tradeNo, $balanceStatus, $balanceFreeze); //递归返佣
                }
            }
        }
    }


    /**
     * @param User|null $customer
     * @param RepertoryItemSku $repertoryItemSku
     * @param int $quantity
     * @return string
     */
    public function getAmount(?User $customer, RepertoryItemSku $repertoryItemSku, int $quantity = 1): string
    {

        //原始价格
        $prices[] = $repertoryItemSku->stock_price;
        /**
         * @var UserGroup $group
         */
        $group = $customer->group ?? null;

        //用户为空，代表是主站
        if (!$customer) {
            return "0";
        }

        //如果商品属于用户自己，那么将自动换算税收比例
        if ($customer->id == $repertoryItemSku->user_id) {
            $decimal = new Decimal((string)$repertoryItemSku->supply_price, 6); //成本价 * 税率
            return $decimal->mul((string)$group->tax_ratio)->getAmount();
        }

        /**
         * 查询批发价格
         * @var RepertoryItemSkuWholesale $repertoryItemSkuWholesale
         */
        $repertoryItemSkuWholesale = RepertoryItemSkuWholesale::query()->where("sku_id", $repertoryItemSku->id)->where("quantity", "<=", $quantity)->orderBy("quantity", "desc")->first();
        if ($repertoryItemSkuWholesale) {
            $prices[] = $repertoryItemSkuWholesale->stock_price;

            if ($group) {
                /**
                 * 用户组批发
                 * @var RepertoryItemSkuWholesaleGroup $repertoryItemSkuWholesaleGroup
                 */
                $repertoryItemSkuWholesaleGroup = RepertoryItemSkuWholesaleGroup::query()->where("wholesale_id", $repertoryItemSkuWholesale->id)->where("group_id", $group->id)->where("status", 1)->first();
                if ($repertoryItemSkuWholesaleGroup) {
                    $prices[] = $repertoryItemSkuWholesaleGroup->stock_price;
                }
            }

            /**
             * 密价批发
             * @var RepertoryItemSkuWholesaleUser $repertoryItemSkuWholesaleUser
             */
            $repertoryItemSkuWholesaleUser = RepertoryItemSkuWholesaleUser::query()->where("customer_id", $customer->id)->where("wholesale_id", $repertoryItemSkuWholesale->id)->where("status", 1)->first();
            if ($repertoryItemSkuWholesaleUser) {
                $prices[] = $repertoryItemSkuWholesaleUser->stock_price;
            }
        }


        if ($group) {
            /**
             * 用户组价格
             * @var RepertoryItemSkuGroup $repertoryItemSkuGroup
             */
            $repertoryItemSkuGroup = RepertoryItemSkuGroup::query()->where("group_id", $group->id)->where("sku_id", $repertoryItemSku->id)->where("status", 1)->first();
            if ($repertoryItemSkuGroup) {
                $prices[] = $repertoryItemSkuGroup->stock_price;

            }
        }

        /**
         * 密价
         * @var RepertoryItemSkuUser $repertoryItemSkuUser
         */
        $repertoryItemSkuUser = RepertoryItemSkuUser::query()->where("customer_id", $customer->id)->where("sku_id", $repertoryItemSku->id)->where("status", 1)->first();
        if ($repertoryItemSkuUser) {
            $prices[] = $repertoryItemSkuUser->stock_price;
        }

        sort($prices);

        return (string)array_shift($prices);
    }

    /**
     * @param int|Order $order
     * @return Ship|null
     */
    public function getOrderShip(int|\App\Model\RepertoryOrder $order): ?Ship
    {
        if (is_int($order)) {
            $order = \App\Model\RepertoryOrder::query()->find($order);
        }
        if (!$order) {
            return null;
        }
        return $this->ship->getShip($order->repertory_item_sku_id, $order);
    }
}