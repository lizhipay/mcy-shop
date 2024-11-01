<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

use App\Entity\Shop\Wholesale;
use Kernel\Component\ToArray;

class RepertoryItemSku
{
    use ToArray;

    public int $id;
    public string $name;
    public string $pictureUrl;
    public string $pictureThumbUrl;
    public string $stockPrice;
    public int $marketControlStatus;
    public string $marketControlMinPrice;
    public string $marketControlMaxPrice;
    public ?RepertoryItem $repertoryItem = null;
    public mixed $stock = null;

    /**
     * @var Wholesale[]
     */
    public array $wholesale = [];

    /**
     * @var bool
     */
    public bool $haveWholesale = false;


    /**
     * @param \App\Model\RepertoryItemSku $repertoryItemSku
     */
    public function __construct(\App\Model\RepertoryItemSku $repertoryItemSku)
    {
        $this->id = $repertoryItemSku->id;
        $this->name = $repertoryItemSku->name;
        $this->pictureUrl = $repertoryItemSku->picture_url;
        $this->pictureThumbUrl = $repertoryItemSku->picture_thumb_url;
        $this->stockPrice = (string)$repertoryItemSku->stock_price;
        $this->marketControlStatus = (int)$repertoryItemSku->market_control_status;
        $this->marketControlMaxPrice = (string)$repertoryItemSku->market_control_max_price;
        $this->marketControlMinPrice = (string)$repertoryItemSku->market_control_min_price;
    }


    /**
     * @param RepertoryItem $repertoryItem
     */
    public function setRepertoryItem(RepertoryItem $repertoryItem): void
    {
        $this->repertoryItem = $repertoryItem;
    }


    /**
     * @param array $wholesale
     */
    public function setWholesale(array $wholesale): void
    {
        if (!empty($wholesale)) {
            $this->haveWholesale = true;
        }
        $this->wholesale = $wholesale;
    }

    /**
     * @param mixed $stock
     */
    public function setStock(mixed $stock): void
    {
        $this->stock = $stock;
    }
}