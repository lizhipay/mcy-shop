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

    /**
     * @var bool
     */
    public bool $marketControl = false;


    /**
     * @var string
     */
    public string $marketControlMinPrice = "0";

    /**
     * @var string
     */
    public string $marketControlMaxPrice = "0";

    /**
     * @var string
     */
    public string $marketControlLevelMinPrice = "0";

    /**
     * @var string
     */
    public string $marketControlLevelMaxPrice = "0";

    /**
     * @var string
     */
    public string $marketControlUserMinPrice = "0";

    /**
     * @var string
     */
    public string $marketControlUserMaxPrice = "0";

    /**
     * @var int
     */
    public int $marketControlMinNum = 0;

    /**
     * @var int
     */
    public int $marketControlMaxNum = 0;

    /**
     * @var int
     */
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
     * @param bool $marketControl
     */
    public function setMarketControl(bool $marketControl): void
    {
        $this->marketControl = $marketControl;
    }

    /**
     * @param string $marketControlMinPrice
     */
    public function setMarketControlMinPrice(string $marketControlMinPrice): void
    {
        $this->marketControlMinPrice = $marketControlMinPrice;
    }


    /**
     * @param string $marketControlMaxPrice
     */
    public function setMarketControlMaxPrice(string $marketControlMaxPrice): void
    {
        $this->marketControlMaxPrice = $marketControlMaxPrice;
    }

    /**
     * @param string $marketControlLevelMaxPrice
     */
    public function setMarketControlLevelMaxPrice(string $marketControlLevelMaxPrice): void
    {
        $this->marketControlLevelMaxPrice = $marketControlLevelMaxPrice;
    }

    /**
     * @param string $marketControlLevelMinPrice
     */
    public function setMarketControlLevelMinPrice(string $marketControlLevelMinPrice): void
    {
        $this->marketControlLevelMinPrice = $marketControlLevelMinPrice;
    }

    /**
     * @param string $marketControlUserMaxPrice
     */
    public function setMarketControlUserMaxPrice(string $marketControlUserMaxPrice): void
    {
        $this->marketControlUserMaxPrice = $marketControlUserMaxPrice;
    }

    /**
     * @param string $marketControlUserMinPrice
     */
    public function setMarketControlUserMinPrice(string $marketControlUserMinPrice): void
    {
        $this->marketControlUserMinPrice = $marketControlUserMinPrice;
    }

    /**
     * @param int $marketControlMinNum
     */
    public function setMarketControlMinNum(int $marketControlMinNum): void
    {
        $this->marketControlMinNum = $marketControlMinNum;
    }

    /**
     * @param int $marketControlMaxNum
     */
    public function setMarketControlMaxNum(int $marketControlMaxNum): void
    {
        $this->marketControlMaxNum = $marketControlMaxNum;
    }

    /**
     * @param int $marketControlOnlyNum
     */
    public function setMarketControlOnlyNum(int $marketControlOnlyNum): void
    {
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