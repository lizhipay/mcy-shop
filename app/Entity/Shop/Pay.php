<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class Pay
{
    use ToArray;

    public string $tradeNo;
    public string $orderAmount;
    public string $tradeAmount = "0";
    public string $balanceAmount = "0";
    public int $status;
    public string $createTime;
    public string $payUrl;

    public function getTradeNo(): string
    {
        return $this->tradeNo;
    }

    public function setTradeNo(string $tradeNo): void
    {
        $this->tradeNo = $tradeNo;
    }

    public function getOrderAmount(): string
    {
        return $this->orderAmount;
    }

    public function setOrderAmount(string $orderAmount): void
    {
        $this->orderAmount = $orderAmount;
    }

    public function getTradeAmount(): string
    {
        return $this->tradeAmount;
    }

    public function setTradeAmount(string $tradeAmount): void
    {
        $this->tradeAmount = $tradeAmount;
    }

    public function getBalanceAmount(): string
    {
        return $this->balanceAmount;
    }

    public function setBalanceAmount(string $balanceAmount): void
    {
        $this->balanceAmount = $balanceAmount;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function setCreateTime(string $createTime): void
    {
        $this->createTime = $createTime;
    }

    public function getPayUrl(): string
    {
        return $this->payUrl;
    }

    public function setPayUrl(string $payUrl): void
    {
        $this->payUrl = $payUrl;
    }
}