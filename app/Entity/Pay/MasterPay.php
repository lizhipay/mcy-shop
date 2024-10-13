<?php
declare (strict_types=1);

namespace App\Entity\Pay;

use Kernel\Component\ToArray;

class MasterPay
{

    use ToArray;

    public int $id;
    public string $name;
    public string $icon;
    public string $fee;
    public array $scope = [];


    public function __construct(int $id, string $name, string $icon, string|float|int $fee, array $scope)
    {
        $this->id = $id;
        $this->name = $name;
        $this->icon = $icon;
        $this->fee = (string)$fee;
        $this->scope = $scope;
    }


    /**
     * @param string|float|int $fee
     */
    public function setFee(string|float|int $fee): void
    {
        $this->fee = (string)$fee;
    }
}