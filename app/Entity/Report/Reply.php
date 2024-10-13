<?php
declare (strict_types=1);

namespace App\Entity\Report;

class Reply
{
    //维权订单ID
    public int $reportId;

    //回复内容
    public string $message;

    //相关图片
    public ?string $imageUrl = null;

    //用户ID
    public int $userId;


    /**
     * @param int $userId
     * @param int $reportId
     * @param string $message
     */
    public function __construct(int $userId, int $reportId, string $message)
    {
        $this->userId = $userId;
        $this->reportId = $reportId;
        $this->message = $message;
    }


    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }
}