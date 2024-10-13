<?php
declare (strict_types=1);

namespace App\Entity\Pay;

use App\Model\PayOrder;
use Kernel\Component\ToArray;

class Order
{
    use ToArray;

    public string $orderAmount;
    public string $tradeAmount;
    public string $balanceAmount;
    public int $status;
    public string $createTime;
    public int $renderMode;
    public string $timeout;
    public string $payUrl;

    public ?Pay $pay = null;

    public string $fee;
    public string $apiFee;

    public function __construct(PayOrder $payOrder)
    {
        $this->orderAmount = (string)$payOrder->order_amount;
        $this->tradeAmount = (string)$payOrder->trade_amount;
        $this->balanceAmount = (string)$payOrder->balance_amount;
        $this->fee = (string)$payOrder->fee;
        $this->apiFee = (string)$payOrder->api_fee;
        $this->status = $payOrder->status;
        $this->createTime = $payOrder->create_time;
        $this->renderMode = $payOrder->render_mode;
        $this->timeout = $payOrder->timeout;
        $this->payUrl = $payOrder->pay_url;
    }

    /**
     * @param Pay $pay
     * @return void
     */
    public function setPay(Pay $pay): void
    {
        $this->pay = $pay;
    }
}