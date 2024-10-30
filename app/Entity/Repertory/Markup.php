<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

use App\Model\RepertoryItemMarkupTemplate;
use Kernel\Util\Decimal;

class Markup
{
    public string $percentage = "0"; //加价百分比
    public bool $syncAmount = true;
    public bool $syncName = true;
    public bool $syncIntroduce = true;
    public bool $syncPicture = true;
    public bool $syncSkuName = true;
    public bool $syncSkuPicture = true;
    public bool $syncRemoteDownload = false;
    public string $exchangeRate = "0";
    public string $keepDecimals = "2";

    public function __construct(?RepertoryItemMarkupTemplate $itemMarkupTemplate = null)
    {
        if ($itemMarkupTemplate) {
            $this->syncAmount = (bool)$itemMarkupTemplate->sync_amount;
            $this->syncName = (bool)$itemMarkupTemplate->sync_name;
            $this->syncIntroduce = (bool)$itemMarkupTemplate->sync_introduce;
            $this->syncPicture = (bool)$itemMarkupTemplate->sync_picture;
            $this->syncSkuName = (bool)$itemMarkupTemplate->sync_sku_name;
            $this->syncSkuPicture = (bool)$itemMarkupTemplate->sync_sku_picture;
            $this->syncRemoteDownload = (bool)$itemMarkupTemplate->sync_remote_download;
            $this->exchangeRate = (string)$itemMarkupTemplate->exchange_rate;
            $this->keepDecimals = (string)$itemMarkupTemplate->keep_decimals;

            if ($itemMarkupTemplate->sync_amount != 1) {
                $this->setPercentage("0");
                return;
            }

            if ($itemMarkupTemplate->drift_model == 0) {
                $this->setPercentage((string)$itemMarkupTemplate->drift_value);
                return;
            }

            if ($itemMarkupTemplate->drift_value > 0) {
                $decimal = new Decimal((string)$itemMarkupTemplate->drift_value, 6);
                $this->setPercentage($decimal->div($itemMarkupTemplate->drift_base_amount)->getAmount(6));
            } else {
                $this->setPercentage("0");
            }
        }
    }


    /**
     * @param string $exchangeRate
     */
    public function setExchangeRate(string $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * @param string $keepDecimals
     */
    public function setKeepDecimals(string $keepDecimals): void
    {
        $this->keepDecimals = $keepDecimals;
    }

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

    public function setSyncRemoteDownload(bool $syncRemoteDownload): void
    {
        $this->syncRemoteDownload = $syncRemoteDownload;
    }
}