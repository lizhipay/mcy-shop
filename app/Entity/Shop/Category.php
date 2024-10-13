<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class Category
{
    use ToArray;

    public int $id;
    public string $name;
    public string $icon;


    /**
     * @param int $id
     * @param string $name
     * @param string $icon
     */
    public function __construct(int $id, string $name, string $icon)
    {
        $this->id = $id;
        $this->name = $name;
        $this->icon = (string)$icon;
    }
}