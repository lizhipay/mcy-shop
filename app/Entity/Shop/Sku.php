<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use App\Model\ItemSku;
use Kernel\Component\ToArray;

class Sku
{
    use ToArray;

    public int $id;
    public int $repertoryItemSkuId;
    public string $name;
    public ?string $price = null;
    public mixed $stock = null;
    public ?bool $stockAvailable = null;
    public string $pictureUrl;
    public string $thumbUrl;

    public ?QuantityRestriction $quantityRestriction = null;

    /**
     * @var Wholesale[]
     */
    public array $wholesale = [];

    /**
     * @var bool
     */
    public bool $haveWholesale = false;


    /**
     * @param ItemSku $sku
     */
    public function __construct(ItemSku $sku)
    {
        $this->id = $sku->id;
        $this->name = $sku->name;
        $this->pictureUrl = (string)$sku->picture_url;
        $this->thumbUrl = (string)$sku->picture_thumb_url;
        $this->repertoryItemSkuId = $sku->repertory_item_sku_id;
    }

    /**
     * @param bool|null $stockAvailable
     */
    public function setStockAvailable(bool $stockAvailable): void
    {
        $this->stockAvailable = $stockAvailable;
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    public function setStock(mixed $stock): void
    {
        $this->stock = $stock;
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
     * @param QuantityRestriction $quantityRestriction
     */
    public function setQuantityRestriction(QuantityRestriction $quantityRestriction): void
    {
        $this->quantityRestriction = $quantityRestriction;
    }
}