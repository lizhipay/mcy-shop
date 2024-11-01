<?php
declare (strict_types=1);

namespace Kernel\Plugin\Entity;

use Kernel\Component\ToArray;
use Kernel\Waf\Firewall;

class Sku
{
    use ToArray;

    public string $name;
    public string $pictureUrl;
    public string $price;
    public ?string $cost = null;
    public array $options = [];
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

    /**
     * @var string
     */
    public string $uniqueId;


    /**
     * @var array
     */
    public array $versions = [];

    /**
     * @param string|int|float $uniqueId
     * @param string $name
     * @param string $pictureUrl
     * @param string|int|float $price
     */
    public function __construct(string|int|float $uniqueId, string $name, string $pictureUrl, string|int|float $price)
    {
        $this->name = strip_tags($name);
        $this->pictureUrl = strip_tags($pictureUrl);
        $this->price = strip_tags((string)$price);
        $this->uniqueId = md5((string)$uniqueId);

        $this->versions["name"] = md5((string)$this->name);
        $this->versions["price"] = md5((string)$this->price);
        $this->versions["picture_url"] = md5($this->pictureUrl);
    }

    /**
     * @param array $options
     * @throws \ReflectionException
     */
    public function setOptions(array $options): void
    {
        $this->options = Firewall::inst()->xssKiller($options);
    }


    /**
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
        $this->versions["price"] = md5($price);
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
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }


    /**
     * @param string|int|float|null $cost
     */
    public function setCost(string|int|float|null $cost): void
    {
        if ($cost === null) {
            return;
        }
        $this->cost = (string)$cost;
    }
}