<?php
declare (strict_types=1);

namespace App\Entity\Pay;

use Kernel\Component\ToArray;

class Pay
{
    use ToArray;

    public int $id;
    public string $name;
    public string $icon;


    public function __construct(\App\Model\Pay $pay)
    {
        $this->id = $pay->id;
        $this->name = $pay->name;
        $this->icon = (string)$pay->icon;
    }
}