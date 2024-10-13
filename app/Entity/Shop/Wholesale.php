<?php
declare(strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;
use Kernel\Util\Str;

class Wholesale
{
    use ToArray;

    public int $id;
    public int $quantity;
    public string $price;

    public function __construct(int $id, int $quantity, string|int|float $price)
    {
        $this->id = $id;
        $this->quantity = $quantity;
        $this->price = Str::amountRemoveTrailingZeros($price);
    }


    /**
     * @param string|int|float $price
     */
    public function setPrice(string|int|float $price): void
    {
        $this->price = Str::amountRemoveTrailingZeros($price);
    }
}