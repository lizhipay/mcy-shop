<?php
declare (strict_types=1);

namespace App\Entity\Config;

use Kernel\Component\ToArray;

class Currency
{
    use ToArray;


    public string $code;
    public string $symbol;
    public string $name;
    //汇率
    public ?string $rate = null;


    public function __construct(string $code, string $symbol, string $name)
    {
        $this->code = $code;
        $this->symbol = $symbol;
        $this->name = $name;
    }


    /**
     * @param float|string $rate
     */
    public function setRate(float|string $rate): void
    {
        $this->rate = (string)$rate;
    }
}