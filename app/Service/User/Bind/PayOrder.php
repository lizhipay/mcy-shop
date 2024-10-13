<?php
declare (strict_types=1);

namespace App\Service\User\Bind;

use App\Entity\Shop\Pay;
use App\Model\Config;
use App\Model\Order;
use App\Model\PluginConfig;
use App\Model\User;
use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Response;
use Kernel\Database\Db;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Exception\ServiceException;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Date;
use Kernel\Util\Decimal;

class PayOrder implements \App\Service\User\PayOrder
{

    #[Inject]
    private \App\Service\User\Order $order;


    #[Inject]
    private \App\Service\User\Balance $balance;


    #[Inject]
    private \App\Service\User\Pay $pay;

    #[Inject]
    private \App\Service\Common\Config $config;

    /**
     * @param int $payId
     * @return \App\Model\Pay
     * @throws JSONException
     */
    public function getPay(int $payId): \App\Model\Pay
    {
        /**
         * @var \App\Model\Pay $payApi
         */
        $payApi = \App\Model\Pay::with(["config"])->find($payId);

        if (!$payApi) {
            throw new JSONException("该支付方式不存在");
        }

        if ($payApi->status != 1) {
            throw new JSONException("该支付方式未启用");
        }

        if ($payApi->pid > 0) {
            return $this->getPay($payApi->pid);
        }

        return $payApi;
    }


    /**
     * @param string $tradeNo
     * @param int $method
     * @param bool $balance
     * @param string $tradeIp
     * @param string $httpUrl
     * @param User|null $customer
     * @return Pay
     * @throws \Exception|\Throwable
     */
    public function pay(string $tradeNo, int $method, bool $balance, string $tradeIp, string $httpUrl, ?User $customer = null): Pay
    {
        return Db::transaction(function () use ($httpUrl, $balance, $tradeIp, $tradeNo, $method, $customer) {
            /**
             * @var Order $order
             */
            $order = Order::query()->where("trade_no", $tradeNo)->first();

            if (!$order) {
                throw new JSONException("订单不存在");
            }

            if ($order->status != 0) {
                throw new JSONException("订单状态异常");
            }

            if (strtotime($order->create_time) + 3600 < time()) {
                throw new JSONException("订单已超时");
            }

            $order->status = 3; //正在付款
            $order->save();


            $payOrder = new \App\Model\PayOrder();
            $payOrder->order_id = $order->id;
            $payOrder->order_amount = $order->total_amount;
            $payOrder->status = 0;
            $payOrder->create_time = Date::current();
            $payOrder->trade_amount = $order->total_amount;
            $payOrder->pay_id = $method;
            $payOrder->timeout = \date("Y-m-d H:i:s", time() + 3600);
            $payOrder->pay_url = "#";
            $payOrder->fee = 0;
            $payOrder->api_fee = 0;

            $order->user_id && $payOrder->user_id = $order->user_id;
            $customer && $payOrder->customer_id = $customer->id;

            $pay = new Pay();
            $pay->setTradeNo($tradeNo);
            $pay->setOrderAmount((string)$payOrder->order_amount);
            $pay->setCreateTime($payOrder->create_time);
            $pay->setStatus($payOrder->status);


            //除开充值功能外，可使用余额购买
            if ($customer && $order->type != \App\Const\Order::ORDER_TYPE_RECHARGE) {
                /**
                 * @var User $customer
                 */
                $customer = User::query()->find($customer->id);

                if ($method == 0 && $balance && $customer->balance < $payOrder->order_amount) {
                    throw new JSONException("余额不足，请充值后再来购买", 10999);
                }

                if ($balance) {
                    //检测余额是否足够，如果足够，直接发货
                    if ($customer->balance >= $payOrder->order_amount) {
                        $payOrder->trade_amount = 0;
                        $payOrder->balance_amount = $payOrder->order_amount;
                        $payOrder->pay_id = 0;
                        //扣款
                        $this->balance->deduct($customer->id, (string)$payOrder->order_amount, \App\Const\Balance::TYPE_SHOPPING, $order->trade_no);
                        Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($payOrder->user_id), Point::SERVICE_PAY_ORDER_BALANCE_PAY, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $customer, $payOrder, $order);
                        $payOrder->status = 2;
                        $payOrder->pay_time = Date::current();
                        $payOrder->save();
                        //准备发货
                        $this->order->deliver($order, $tradeIp);
                        $pay->setBalanceAmount((string)$payOrder->balance_amount);
                        $pay->setStatus($payOrder->status);
                        $pay->setPayUrl("/pay/sync.{$tradeNo}");
                        return $pay;
                    } elseif ($customer->balance < $payOrder->order_amount && $customer->balance > 0) {
                        //余额不足，使用組合支付
                        $payOrder->balance_amount = $customer->balance;
                        $payOrder->trade_amount = (new Decimal((string)$order->total_amount))->sub((string)$customer->balance)->getAmount(); //该金额则需要通过在线支付
                        $pay->setBalanceAmount((string)$payOrder->balance_amount);
                    }
                }
            }

            //拿到最终需要支付的金额，准备调用插件下单
            $pay->setTradeAmount((string)$payOrder->trade_amount);

            //获取支付配置
            $payApi = $this->getPay($method);
            $isOfficial = $this->pay->isOfficial($method);


            if (!$isOfficial) {
                if ($order->type != \App\Const\Order::ORDER_TYPE_PRODUCT && $order->type != \App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL) {
                    throw new JSONException("此业务不支持自定义支付接口");
                }
                /**
                 * @var User $user
                 */
                $user = User::query()->find($payApi->user_id);
                if (!$user) {
                    throw new JSONException("支付接口异常");
                }

                if ($user->balance < $payOrder->trade_amount) {
                    throw new JSONException("商家余额不足，无法使用自定义支付接口");
                }
            } else if ($user = $order->user) {
                //官方接口
                $masterPay = $this->pay->getMasterPay($payApi->id, $user, $user?->group);
                if (!$masterPay) {
                    throw new JSONException("此接口无法正常使用");
                }

                if ($masterPay->fee > 0) {
                    $payOrder->fee = (new Decimal($payOrder->trade_amount, 3))->mul($masterPay->fee)->getAmount(2); //手续费
                    $payOrder->fee = $payOrder->fee > 0 ? $payOrder->fee : 0.01;
                }
            }

            /**
             * @var PluginConfig $payConfig
             */
            $payConfig = $payApi->config;

            if (!$payConfig) {
                throw new JSONException("该支付方式配置不存在");
            }

            $syncUrl = trim($httpUrl, "/") . "/pay/sync.{$tradeNo}";

            $payCfg = Config::main("pay");
            if (isset($payCfg['async_custom']) && $payCfg['async_custom'] == 1 && isset($payCfg['async_protocol']) && isset($payCfg['async_host'])) {
                $httpUrl = $payCfg['async_protocol'] . "://" . $payCfg['async_host'];
            }

            $asyncUrl = trim($httpUrl, "/") . "/pay/async.{$tradeNo}";

            $currency = $this->config->getCurrency();

            if ($payApi->api_fee > 0) {
                $payOrder->api_fee = (new Decimal($payOrder->trade_amount, 3))->mul($payApi->api_fee)->getAmount(2); //手续费
                $payOrder->api_fee = $payOrder->api_fee > 0 ? $payOrder->api_fee : 0.01;
            }

            $payAmount = (new Decimal($payOrder->trade_amount, 2))->add($payOrder->fee)->add($payOrder->api_fee);
            Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($payOrder->user_id), Point::SERVICE_PAY_ORDER_THIRD_TRADE_BEFORE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $payAmount, $customer, $payOrder, $order);

            /**
             * @var \Kernel\Plugin\Handle\Pay $payHandle
             */
            $payHandle = \Kernel\Plugin\Pay::instance()->handle(
                name: $payConfig->plugin,
                env: Usr::inst()->userToEnv($payConfig->user_id),
                order: $order,
                payOrder: $payOrder,
                config: $payConfig->config,
                code: $payApi->code,
                clientIp: $tradeIp,
                amount: $currency->code == "rmb" ? $payAmount->getAmount(2) : $payAmount->mul($currency->rate)->getAmount(),
                asyncUrl: $asyncUrl,
                syncUrl: $syncUrl
            );

            $payResult = $payHandle->create();
            $payOrder->pay_url = $payResult->getPayUrl();
            $payOrder->render_mode = $payResult->getRenderMode();
            $payOrder->timeout = \date("Y-m-d H:i:s", time() + $payResult->getTimeout());

            $payOrder->save();


            if (!empty($payResult->getOption())) {
                $payOrder->setOption($payResult->getOption());
            }

            Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($payOrder->user_id), Point::SERVICE_PAY_ORDER_THIRD_TRADE_AFTER, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $payResult, $customer, $payOrder, $order);
            $pay->setPayUrl("/pay.{$tradeNo}");
            return $pay;
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE, 3);
    }


    /**
     * @param string $tradeNo
     * @return \App\Entity\Pay\Order
     * @throws JSONException
     */
    public function getPayOrder(string $tradeNo): \App\Entity\Pay\Order
    {
        $order = Order::query()->where("trade_no", $tradeNo)->first();

        if (!$order) {
            throw new JSONException("订单不存在");
        }

        /**
         * @var \App\Model\PayOrder $payOrder
         */
        $payOrder = \App\Model\PayOrder::query()->where("order_id", $order->id)->first();

        if (!$payOrder) {
            throw new JSONException("订单不存在");
        }

        if (strtotime($payOrder->timeout) < time()) {
            throw new JSONException("订单已过期");
        }

        return new \App\Entity\Pay\Order($payOrder);
    }

    /**
     * @param string $tradeNo
     * @param string $clientIp
     * @return Response
     * @throws \Exception|\Throwable
     */
    public function async(string $tradeNo, string $clientIp): Response
    {
        return Db::transaction(function () use ($clientIp, $tradeNo) {
            /**
             * @var Order $order
             */
            $order = Order::query()->where("trade_no", $tradeNo)->first();

            if (!$order) {
                throw new JSONException("订单不存在");
            }

            if ($order->status != 3) { //正在付款
                throw new JSONException("该订单已失效");
            }

            /**
             * @var \App\Model\PayOrder $payOrder
             */
            $payOrder = \App\Model\PayOrder::with(['option'])->where("order_id", $order->id)->first();

            if (!$payOrder) {
                throw new JSONException("订单不存在");
            }

            if ($payOrder->status != 1 && $payOrder->status != 0) {
                throw new JSONException("该订单已失效");
            }

            if (strtotime($payOrder->timeout) < time()) {
                throw new JSONException("该订单已超时");
            }


            $pay = $this->getPay($payOrder->pay_id);


            /**
             * @var PluginConfig $config
             */
            $config = $pay->config;

            if (!$config) {
                throw new JSONException("支付配置文件不存在");
            }

            $currency = $this->config->getCurrency();
            $payAmount = (new Decimal($payOrder->trade_amount, 2))->add($payOrder->fee)->add($payOrder->api_fee);

            Plugin::instance()->unsafeHook(Usr::inst()->userToEnv($payOrder->user_id), Point::SERVICE_PAY_ORDER_ASYNC, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $payAmount, $payOrder, $order);
            /**
             * @var \Kernel\Plugin\Handle\Pay $payHandle
             */
            $payHandle = \Kernel\Plugin\Pay::instance()->handle(
                name: $config->plugin,
                env: Usr::inst()->userToEnv($config->user_id),
                order: $order,
                payOrder: $payOrder,
                config: $config->config,
                code: $pay->code,
                clientIp: $clientIp,
                amount: $currency->code == "rmb" ? $payAmount->getAmount(2) : $payAmount->mul($currency->rate)->getAmount(),
            );
            return $payHandle->async();
        }, \Kernel\Database\Const\Db::ISOLATION_SERIALIZABLE, 3);
    }

    /**
     * @param string $tradeNo
     * @return string
     * @throws ServiceException
     */
    public function getSyncUrl(string $tradeNo): string
    {
        /**
         * @var Order $order
         */
        $order = Order::query()->where("trade_no", $tradeNo)->first();

        if (!$order) {
            throw new ServiceException("订单不存在");
        }

        if ($order->type === \App\Const\Order::ORDER_TYPE_PRODUCT) {
            return "/search?tradeNo={$tradeNo}";
        } elseif ($order->type === \App\Const\Order::ORDER_TYPE_RECHARGE) {
            return "/user/recharge";
        } elseif ($order->type === \App\Const\Order::ORDER_TYPE_UPGRADE_GROUP) {
            return "/user/merchant/open";
        } elseif ($order->type === \App\Const\Order::ORDER_TYPE_UPGRADE_LEVEL) {
            return "/user/personal";
        } else {
            //TODO : 这里需要实现插件的同步地址
            return "/";
        }
    }

    /**
     * @param int $orderId
     * @return \App\Model\PayOrder
     * @throws RuntimeException
     */
    public function findPayOrder(int $orderId): \App\Model\PayOrder
    {
        /**
         * @var \App\Model\PayOrder $payOrder
         */
        $payOrder = \App\Model\PayOrder::query()->where("order_id", $orderId)->first();

        if (!$payOrder) {
            throw new RuntimeException("订单不存在");
        }
        return $payOrder;
    }
}