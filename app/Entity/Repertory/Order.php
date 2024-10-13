<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

use App\Model\User;
use Kernel\Util\Str;

class Order
{
    /**
     * 顾客
     * @var User
     */
    public User $customer;


    /**
     * 24位订单号
     * @var string
     */
    public string $tradeNo;


    /**
     * 仓库中的物品ID
     * @var int
     */
    public int $repertoryItemId;

    /**
     * 物品的SKU ID
     * @var int
     */
    public int $repertoryItemSkuId;


    /**
     * 下订数量
     * @var int
     */
    public int $quantity;


    /**
     * 客户IP
     * @var string
     */
    public string $tradeIp;


    public function __construct(User $customer, int $repertoryItemId, int $repertoryItemSkuId, int $quantity, string $tradeIp, ?string $tradeNo = null)
    {
        $this->customer = $customer;
        $this->repertoryItemId = $repertoryItemId;
        $this->repertoryItemSkuId = $repertoryItemSkuId;
        $this->quantity = $quantity;
        $this->tradeIp = $tradeIp;
        if (!$tradeNo) {
            $tradeNo = Str::generateTradeNo();
        }
        $this->tradeNo = $tradeNo;
    }
}