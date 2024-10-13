<?php
declare(strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class QuantityRestriction
{
    use ToArray;

    /**
     * @var int
     */
    public int $min = 1;

    /**
     * @var int
     */
    public int $max = 0;


    /**
     * @var int
     */
    public int $total = 0;


    /**
     * @param int $min
     * @param int $max
     * @param int $total
     */
    public function __construct(int $min = 1, int $max = 0, int $total = 0)
    {
        $this->min = $min > 0 ? $min : 1;
        $this->max = $max;
        $this->total = $total;
    }


    /**
     * @param int $min
     */
    public function setMin(int $min): void
    {
        $this->min = $min > 0 ? $min : 1;
    }

    /**
     * @param int $max
     */
    public function setMax(int $max): void
    {
        $this->max = $max;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}