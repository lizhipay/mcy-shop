<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class OrderItem
{
    use ToArray;

    public int $id;
    public ?Item $item = null;
    public ?Sku $sku = null;
    public int $quantity;
    public string $amount;
    public int $status;
    public ?string $treasure = null;
    public ?array $widget = null;
    public bool $render = false;
    public ?string $message = null;

    public function __construct(\App\Model\OrderItem $orderItem)
    {
        $this->id = $orderItem->id;
        $this->quantity = $orderItem->quantity;
        $this->amount = (string)$orderItem->amount;
        $this->status = $orderItem->status;
    }


    public function setItem(Item $item): void
    {
        $this->item = $item;
    }

    public function setSku(Sku $sku): void
    {
        $this->sku = $sku;
    }

    public function setTreasure(string $treasure): void
    {
        $this->treasure = $treasure;
    }

    public function setWidget(array $widget): void
    {
        $this->widget = $widget;
    }


    public function setRender(bool $render): void
    {
        $this->render = $render;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
}