<?php
declare (strict_types=1);

namespace App\Entity\Report;

class Order
{
    public int $orderItemId;
    public int $customerId;
    public int $type;
    public int $expect;
    public string $message;
    public ?string $imageUrl = null;


    /**
     * @param int $orderItemId
     * @param int $customerId
     * @param int $type
     * @param int $expect
     * @param string $message
     */
    public function __construct(int $orderItemId, int $customerId, int $type, int $expect, string $message)
    {
        $this->orderItemId = $orderItemId;
        $this->customerId = $customerId;
        $this->type = $type;
        $this->expect = $expect;
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