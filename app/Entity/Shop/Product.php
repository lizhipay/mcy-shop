<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class Product
{

    use ToArray;

    public string $icon;
    public string $name;
    public int $quantity;


    public function __construct(string $icon, string $name, int $quantity)
    {
        $this->icon = $icon;
        $this->name = $name;
        $this->quantity = $quantity;
    }
}