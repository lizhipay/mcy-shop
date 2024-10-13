<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

use Kernel\Component\ToArray;

class RepertoryItem
{

    use ToArray;

    public int $id;
    public string $name;
    public ?string $introduce = null;
    public string $pictureUrl;
    public string $pictureThumbUrl;
    public array $widget = [];
    public array $attr = [];


    /**
     * @var RepertoryItemSku[]
     */
    public array $skus = [];

    /**
     * @var bool
     */
    public bool $haveWholesale = false;

    /**
     * @param \App\Model\RepertoryItem $repertoryItem
     */
    public function __construct(\App\Model\RepertoryItem $repertoryItem)
    {
        $this->id = $repertoryItem->id;
        $this->name = $repertoryItem->name;
        $this->pictureUrl = $repertoryItem->picture_url;
        $this->pictureThumbUrl = $repertoryItem->picture_thumb_url;
    }


    /**
     * @param array $widget
     */
    public function setWidget(array $widget): void
    {
        $this->widget = $widget;
    }

    /**
     * @param array $attr
     */
    public function setAttr(array $attr): void
    {
        $this->attr = $attr;
    }


    /**
     * @param string|null $introduce
     */
    public function setIntroduce(?string $introduce): void
    {
        $this->introduce = (string)$introduce;
    }

    /**
     * @param array $skus
     */
    public function setSkus(array $skus): void
    {
        $this->skus = $skus;
    }


    /**
     * @param bool $haveWholesale
     */
    public function setHaveWholesale(bool $haveWholesale): void
    {
        $this->haveWholesale = $haveWholesale;
    }
}