<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

class Deliver
{
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


    public function __construct(string $tradeNo, string $contents, string $amount)
    {
        $this->tradeNo = $tradeNo;
        $this->contents = $contents;
        $this->amount = $amount;
    }
}