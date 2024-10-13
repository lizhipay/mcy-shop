<?php
declare (strict_types=1);

namespace App\Service\User;

use App\Entity\Shop\Pay;
use App\Model\User;
use Kernel\Annotation\Bind;
use Kernel\Context\Interface\Response;

#[Bind(class: \App\Service\User\Bind\PayOrder::class)]
interface PayOrder
{

    /**
     * 获取支付接口
     * @param int $payId
     * @return \App\Model\Pay
     */
    public function getPay(int $payId): \App\Model\Pay;

    /**
     * 发起支付
     * @param string $tradeNo
     * @param int $method
     * @param bool $balance
     * @param string $tradeIp
     * @param string $httpUrl
     * @param User|null $customer
     * @return Pay
     */
    public function pay(string $tradeNo, int $method, bool $balance, string $tradeIp, string $httpUrl, ?User $customer = null): Pay;

    /**
     * 此方法要保证绝对HTTP调用
     * @param string $tradeNo
     * @param string $clientIp
     * @return Response
     */
    public function async(string $tradeNo, string $clientIp): Response;

    /**
     * @param string $tradeNo
     * @return string
     */
    public function getSyncUrl(string $tradeNo): string;


    /**
     * 获取订单信息
     * @param string $tradeNo
     * @return \App\Entity\Pay\Order
     */
    public function getPayOrder(string $tradeNo): \App\Entity\Pay\Order;


    /**
     * @param int $orderId
     * @return \App\Model\PayOrder
     */
    public function findPayOrder(int $orderId): \App\Model\PayOrder;
}