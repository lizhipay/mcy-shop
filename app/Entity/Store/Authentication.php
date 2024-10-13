<?php
declare (strict_types=1);

namespace App\Entity\Store;

use Kernel\Component\ToArray;

class Authentication
{
    use ToArray;

    public int $id;
    public string $key;
    public bool $substation = false;

    public function __construct(int $id, string $key, bool $substation)
    {
        $this->id = $id;
        $this->key = $key;
        $this->substation = $substation;
    }
}