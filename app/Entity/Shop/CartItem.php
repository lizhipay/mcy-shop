<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class CartItem
{
    use ToArray;

    public int $id;
    public int $quantity;

    public ?Sku $sku = null;
    public ?Item $item = null;

    public string $amount;
    public string $price;
    public array $option = [];
    public string $createTime;


    public function __construct(\App\Model\CartItem $cartItem)
    {
        $this->id = $cartItem->id;
        $this->quantity = $cartItem->quantity;
        $this->amount = (string)$cartItem->amount;
        $this->option = $cartItem->option;
        $this->createTime = $cartItem->create_time;
        $this->price = (string)$cartItem->price;
    }

    public function setSku(Sku $sku): void
    {
        $this->sku = $sku;
    }

    public function setItem(Item $item): void
    {
        $this->item = $item;
    }
}