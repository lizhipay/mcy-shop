<?php
declare (strict_types=1);

namespace App\Entity\User;

use Kernel\Component\ToArray;

class BankCard
{
    use ToArray;

    public int $id;
    public string $bankName;
    public string $bankIcon;
    public string $cardNo;


    public function __construct(int $id, string $bankName, string $bankIcon, string $cardNo)
    {
        $this->id = $id;
        $this->bankName = $bankName;
        $this->bankIcon = $bankIcon;
        $this->cardNo = $cardNo;
    }
}