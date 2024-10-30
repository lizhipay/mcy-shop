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

    public bool $marketControlStatus = false;
    //单次最少购买数量
    public int $marketControlMinNum = 0;
    //单次最大购买数量
    public int $marketControlMaxNum = 0;
    //总共最多购买数量
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