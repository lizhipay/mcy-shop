<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

use App\Model\RepertoryOrder;
use Kernel\Component\ToArray;

class Deliver
{
    use ToArray;

    /**
     * 发货内容
     * @var string
     */
    public string $contents;


    /**
     * 订单号
     * @var string
     */
    public string $tradeNo;


    /**
     * 消费金额
     * @var string
     */
    public string $amount;

    /**
     * @var int
     */
    public int $status;

    /**
     * @var string
     */
    public string $itemTradeNo;

    /**
     * @var string
     */
    public string $tradeTime;


    /**
     * @param RepertoryOrder $repertoryOrder
     */
    public function __construct(RepertoryOrder $repertoryOrder)
    {
        $this->tradeNo = $repertoryOrder->trade_no;
        $this->itemTradeNo = $repertoryOrder->item_trade_no;
        $this->contents = $repertoryOrder->contents;
        $this->amount = (string)$repertoryOrder->amount;
        $this->status = $repertoryOrder->status;
        $this->tradeTime = $repertoryOrder->trade_time;
    }
}