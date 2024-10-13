<?php
declare(strict_types=1);

namespace App\Entity\Repertory;

use Kernel\Component\ToArray;

class Sku
{

    use ToArray;

    /**
     * @var int
     */
    public int $repertoryItemSkuId;


    /**
     * @var string
     */
    public string $name;


    /**
     * @var string
     */
    public string $stockPrice;


    /**
     * @var bool
     */
    public bool $marketControl;


    /**
     * @var string
     */
    public string $marketControlMinPrice;

    /**
     * @var string
     */
    public string $marketControlMaxPrice;

    /**
     * @var string
     */
    public string $marketControlLevelMinPrice;

    /**
     * @var string
     */
    public string $marketControlLevelMaxPrice;
    /**
     * @var string
     */
    public string $marketControlUserMinPrice;

    /**
     * @var string
     */
    public string $marketControlUserMaxPrice;

    /**
     * @var int
     */
    public int $marketControlMinNum;

    /**
     * @var int
     */
    public int $marketControlMaxNum;

    /**
     * @var int
     */
    public int $marketControlOnlyNum;

    /**
     * @param int $id
     * @param string|null $name
     * @param string|float|int $stockPrice
     * @param object $marketControl
     */
    public function __construct(int $id, ?string $name, string|float|int $stockPrice, object $marketControl)
    {
        $this->repertoryItemSkuId = $id;
        $this->name = (string)$name;
        $this->stockPrice = (string)$stockPrice;
        $this->marketControl = $marketControl->market_control_status == 1;
        $this->marketControlMaxPrice = $marketControl->market_control_max_price;
        $this->marketControlMinPrice = $marketControl->market_control_min_price;
        $this->marketControlMinNum = $marketControl->market_control_min_num;
        $this->marketControlMaxNum = $marketControl->market_control_max_num;
        $this->marketControlOnlyNum = $marketControl->market_control_only_num;
        $this->marketControlUserMinPrice = $marketControl->market_control_user_min_price;
        $this->marketControlUserMaxPrice = $marketControl->market_control_user_max_price;
        $this->marketControlLevelMinPrice = $marketControl->market_control_level_min_price;
        $this->marketControlLevelMaxPrice = $marketControl->market_control_level_max_price;
    }
}