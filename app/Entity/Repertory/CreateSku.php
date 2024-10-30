<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

class CreateSku
{
    public string $name;
    public string $pictureUrl;
    public string $pictureThumbUrl;

    public string $price;

    public ?string $cost = null;

    public ?string $message = null;

    public bool $marketControlStatus = false;
    //单次最少购买数量
    public int $marketControlMinNum = 0;
    //单次最大购买数量
    public int $marketControlMaxNum = 0;
    //总共最多购买数量
    public int $marketControlOnlyNum = 0;

    public array $pluginData = [];

    /**
     * @var string|null
     */
    public ?string $uniqueId = null;

    /**
     * @var array
     */
    public array $versions = [];


    public function __construct(array $versions, string $name, string $pictureUrl, string $pictureThumbUrl, string $price)
    {
        $this->versions = $versions;
        $this->name = $name;
        $this->pictureUrl = $pictureUrl;
        $this->pictureThumbUrl = $pictureThumbUrl;
        $this->price = $price;
    }

    /**
     * @param string|int|float|null $uniqueId
     */
    public function setUniqueId(null|string|int|float $uniqueId): void
    {
        $this->uniqueId = (string)$uniqueId;
    }


    /**
     * @param int $marketControlMinNum
     */
    public function setMarketControlMinNum(int $marketControlMinNum): void
    {
        $this->marketControlStatus = true;
        $this->marketControlMinNum = $marketControlMinNum;
    }

    /**
     * @param int $marketControlMaxNum
     */
    public function setMarketControlMaxNum(int $marketControlMaxNum): void
    {
        $this->marketControlStatus = true;
        $this->marketControlMaxNum = $marketControlMaxNum;
    }

    /**
     * @param int $marketControlOnlyNum
     */
    public function setMarketControlOnlyNum(int $marketControlOnlyNum): void
    {
        $this->marketControlStatus = true;
        $this->marketControlOnlyNum = $marketControlOnlyNum;
    }

    /**
     * @param array $pluginData
     */
    public function setPluginData(array $pluginData): void
    {
        $this->pluginData = $pluginData;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }


    /**
     * @param string|null $cost
     */
    public function setCost(?string $cost): void
    {
        $this->cost = $cost;
    }
}