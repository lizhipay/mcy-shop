<?php
declare (strict_types=1);

namespace App\Entity\Shop;

class Markup
{
    public string $percentage = "0"; //加价百分比
    public bool $syncAmount = true;
    public bool $syncName = true;
    public bool $syncIntroduce = true;
    public bool $syncPicture = true;
    public bool $syncSkuName = true;
    public bool $syncSkuPicture = true;

    public function setPercentage(string $percentage): void
    {
        $this->percentage = $percentage;
    }

    public function setSyncAmount(bool $syncAmount): void
    {
        $this->syncAmount = $syncAmount;
    }

    public function setSyncName(bool $syncName): void
    {
        $this->syncName = $syncName;
    }

    public function setSyncIntroduce(bool $syncIntroduce): void
    {
        $this->syncIntroduce = $syncIntroduce;
    }

    public function setSyncPicture(bool $syncPicture): void
    {
        $this->syncPicture = $syncPicture;
    }

    public function setSyncSkuName(bool $syncSkuName): void
    {
        $this->syncSkuName = $syncSkuName;
    }

    public function setSyncSkuPicture(bool $syncSkuPicture): void
    {
        $this->syncSkuPicture = $syncSkuPicture;
    }
}