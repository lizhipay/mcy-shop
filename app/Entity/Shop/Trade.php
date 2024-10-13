<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class Trade
{
    use ToArray;

    /**
     * 订单号
     * @var string|null
     */
    public ?string $tradeNo;


    /**
     * 订单总金额
     * @var string|null
     */
    public ?string $totalAmount;


    /**
     * 创建时间
     * @var string|null
     */
    public ?string $createTime;


    /**
     * @var bool
     */
    public bool $isFree = false;


    /**
     * @param string|null $tradeNo
     * @param string|null $totalAmount
     * @param string|null $createTime
     */
    public function __construct(?string $tradeNo = null, ?string $totalAmount = null, ?string $createTime = null)
    {
        $this->tradeNo = $tradeNo;
        $this->totalAmount = $totalAmount;
        $this->createTime = $createTime;
    }


    /**
     * @param bool $isFree
     * @return Trade
     */
    public function setIsFree(bool $isFree): static
    {
        $this->isFree = $isFree;
        return $this;
    }
}