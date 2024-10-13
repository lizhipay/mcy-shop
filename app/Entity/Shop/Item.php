<?php
declare (strict_types=1);

namespace App\Entity\Shop;

use Kernel\Component\ToArray;

class Item
{

    use ToArray;

    public int $id;
    public string $name;
    public string $pictureUrl;
    public string $thumbUrl;
    public int|string|null $stock = null;
    public ?int $sold = null;
    public ?Category $category = null;
    public array $widget = [];
    public array $attr = [];
    public array $source = [];
    /**
     * @var Sku[]
     */
    public array $sku = [];

    /**
     * @var bool
     */
    public bool $haveWholesale = false;

    /**
     * @var int|null
     */
    public ?int $supplierId = null;


    /**
     * @param \App\Model\Item $item
     */
    public function __construct(\App\Model\Item $item)
    {
        $this->id = $item->id;
        $this->name = $item->name;
        $this->pictureUrl = (string)$item->picture_url;
        $this->thumbUrl = (string)$item->picture_thumb_url;
    }

    /**
     * @param int|null $supplierId
     */
    public function setSupplierId(?int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }


    /**
     * @param int|string $stock
     * @return void
     */
    public function setStock(int|string $stock): void
    {
        $this->stock = $stock;
    }

    /**
     * @param int $sold
     * @return void
     */
    public function setSold(int $sold): void
    {
        $this->sold = $sold;
    }

    /**
     * @param Category $category
     * @return void
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @param Sku[] $sku
     * @return void
     */
    public function setSku(array $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @param array $widget
     * @return void
     */
    public function setWidget(array $widget): void
    {
        $this->widget = $widget;
    }

    /**
     * @param array $attr
     * @return void
     */
    public function setAttr(array $attr): void
    {
        $this->attr = $attr;
    }

    /**
     * @param array $source
     * @return void
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }

    /**
     * @param bool $haveWholesale
     */
    public function setHaveWholesale(bool $haveWholesale): void
    {
        $this->haveWholesale = $haveWholesale;
    }
}