<?php
declare (strict_types=1);

namespace App\Entity\User;

use App\Model\UserGroup;

class Group
{
    public int $id;
    public string $icon;
    public string $name;
    public string $price;
    public bool $isMerchant;
    public bool $isSupplier;
    public string $taxRatio;


    public function __construct(UserGroup $group)
    {
        $this->id = $group->id;
        $this->icon = $group->icon;
        $this->name = $group->name;
        $this->price = (string)$group->price;
        $this->isMerchant = (bool)$group->is_merchant;
        $this->isSupplier = (bool)$group->is_supplier;
        $this->taxRatio = (string)($group->tax_ratio * 100);
    }
}