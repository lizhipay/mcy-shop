<?php
declare (strict_types=1);

namespace App\Entity\Repertory;

class CreateItem
{
    public ?int $userId = null;

    public int $categoryId;
    public ?int $shipConfigId = null;
    public string $name;
    public string $introduce = "";
    public string $pictureUrl;
    public string $pictureThumbUrl;

    public string $plugin;

    public array $widget = [];

    public array $attr = [];

    /**
     * @var CreateSku[]
     */
    public array $skus = [];


    public int $refundMode = 0; //退款方式：0=不支持，1=有条件退款，2=无理由退款
    public int $autoReceiptTime = 4320; //单位=分钟

    /**
     * @var string|null
     */
    public ?string $uniqueId = null;

    /**
     * @var array
     */
    public array $versions = [];


    /**
     * @var array
     */
    public array $pluginData = [];

    /**
     * @var int
     */
    public int $markupTemplateId;

    /**
     * @param int $markupTemplateId
     * @param array $versions
     * @param int $categoryId
     * @param string $name
     * @param string $introduce
     * @param string $pictureUrl
     * @param string $pictureThumbUrl
     * @param string $plugin
     * @param CreateSku[] $skus
     */
    public function __construct(int $markupTemplateId, array $versions, int $categoryId, string $name, string $introduce, string $pictureUrl, string $pictureThumbUrl, string $plugin, array $skus)
    {
        $this->versions = $versions;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->introduce = $introduce;
        $this->pictureUrl = $pictureUrl;
        $this->pictureThumbUrl = $pictureThumbUrl;
        $this->plugin = $plugin;
        $this->skus = $skus;
        $this->markupTemplateId = $markupTemplateId;
    }

    /**
     * @param string|int|float|null $uniqueId
     */
    public function setUniqueId(null|string|int|float $uniqueId): void
    {
        $this->uniqueId = (string)$uniqueId;
    }

    /**
     * @param int $shipConfigId
     */
    public function setShipConfigId(int $shipConfigId): void
    {
        $this->shipConfigId = $shipConfigId;
    }

    /**
     * @param array $attr
     */
    public function setAttr(array $attr): void
    {
        $this->attr = $attr;
    }

    /**
     * @param array $widget
     */
    public function setWidget(array $widget): void
    {
        $this->widget = $widget;
    }

    /**
     * @param int $refundMode
     */
    public function setRefundMode(int $refundMode): void
    {
        $this->refundMode = $refundMode;
    }

    /**
     * @param int $autoReceiptTime
     */
    public function setAutoReceiptTime(int $autoReceiptTime): void
    {
        $this->autoReceiptTime = $autoReceiptTime;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @param array $pluginData
     */
    public function setPluginData(array $pluginData): void
    {
        $this->pluginData = $pluginData;
    }
}