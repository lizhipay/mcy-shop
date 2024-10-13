<?php
declare (strict_types=1);

namespace App\Entity\Report;

class Handle
{
    //维权订单ID
    public int $reportId;

    //处理方式
    public int $type;

    //发货信息
    public ?string $treasure = null;

    //退款金额
    public ?string $refundAmount = null;

    //退给商家的金额
    public ?string $refundMerchantAmount = null;

    //回复内容
    public string $message;

    //相关图片
    public ?string $imageUrl = null;


    //回复角色，0=平台，1=供货商，2=消费者
    public int $role;


    /**
     * @param int $reportId
     * @param int $type
     * @param string $message
     * @param int $role
     */
    public function __construct(int $reportId, int $type, string $message, int $role)
    {
        $this->reportId = $reportId;
        $this->type = $type;
        $this->message = $message;
        $this->role = $role;
    }


    /**
     * @param string $refundAmount
     */
    public function setRefundAmount(string $refundAmount): void
    {
        $this->refundAmount = $refundAmount;
    }

    /**
     * @param string $refundMerchantAmount
     */
    public function setRefundMerchantAmount(string $refundMerchantAmount): void
    {
        $this->refundMerchantAmount = $refundMerchantAmount;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }


    /**
     * @param string $treasure
     */
    public function setTreasure(string $treasure): void
    {
        $this->treasure = $treasure;
    }
}