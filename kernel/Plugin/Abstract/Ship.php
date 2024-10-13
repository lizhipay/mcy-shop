<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

use App\Model\PluginConfig;
use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryOrder;
use Kernel\Plugin\Entity\Plugin;

abstract class Ship implements \Kernel\Plugin\Handle\Ship
{

    protected RepertoryItem $item;
    protected RepertoryItemSku $sku;
    protected ?RepertoryOrder $order;
    protected Plugin $plugin;
    protected array $config = []; //如果有货源配置id，则会传递该参数
    protected array $options = [];

    /**
     * @var bool
     */
    protected bool $isCustomRender = false;

    /**
     * @param Plugin $plugin
     * @param RepertoryItem $item
     * @param RepertoryItemSku $sku
     * @param RepertoryOrder|null $order
     */
    public function __construct(Plugin $plugin, RepertoryItem $item, RepertoryItemSku $sku, ?RepertoryOrder $order = null)
    {
        $this->plugin = $plugin;
        $this->item = $item;
        $this->sku = $sku;
        $this->order = $order;

        if ($item->ship_config_id > 0 && $config = PluginConfig::find($item->ship_config_id)) {
            $this->config = is_array($config->config) ? $config->config : [];
        }

        if ($sku->plugin_data) {
            $this->options = (array)json_decode((string)$sku->plugin_data, true) ?: [];
        }
    }


    /**
     * 下单之前的检查
     * @param array $map
     * @return bool
     */
    public function inspection(array $map): bool
    {
        return true;
    }


    /**
     * 是否开启自定义渲染
     * @return bool
     */
    public function isCustomRender(): bool
    {
        return $this->isCustomRender;
    }


    /**
     * 在重写render方法之前，需要重写 isCustomRender 属性为 true，或者直接重写 isCustomRender 方法，返回true，也是可以的
     * @return string
     */
    public function render(): string
    {
        return "write your custom HTML code here";
    }
}