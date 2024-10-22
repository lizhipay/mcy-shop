<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Database\Model;
use Kernel\Log\Log;

/**
 * @property int $id
 * @property int $user_id
 * @property int $repertory_item_id
 * @property string $name
 * @property float $stock_price
 * @property float $supply_price
 * @property int $market_control_status
 * @property float $market_control_min_price
 * @property float $market_control_max_price
 * @property float $market_control_level_min_price
 * @property float $market_control_level_max_price
 * @property float $market_control_user_min_price
 * @property float $market_control_user_max_price
 * @property int $market_control_min_num
 * @property int $market_control_max_num
 * @property int $market_control_only_num
 * @property float $cost
 * @property int $sort
 * @property int $private_display
 * @property string $create_time
 * @property string $temp_id
 * @property string $plugin_data
 * @property string $picture_url
 * @property string $picture_thumb_url
 * @property string $message
 * @property string $unique_id
 * @property array $version
 */
class RepertoryItemSku extends Model
{

    use \Kernel\Component\Inject;

    /**
     * @var ?string
     */
    protected ?string $table = "repertory_item_sku";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'market_control_only_num' => 'integer', 'private_display' => 'integer', 'market_control_max_num' => 'integer', 'market_control_min_num' => 'integer', 'repertory_item_id' => 'integer', 'market_control_status' => 'integer', 'sort' => 'integer', 'version' => 'json'];


    #[Inject]
    protected \App\Service\User\Item $item;

    /**
     * 同步所有对接站点
     * @return void
     * @throws \ReflectionException
     */
    public function saved(): void
    {
        try {
            $this->repertory_item_id && $this->item->syncRepertoryItems($this->repertory_item_id);
        } catch (\Throwable $e) {
            Log::inst()->error("商品同步失败：{$e->getMessage()}");
        }
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function deleted(): void
    {
        try {
            $this->repertory_item_id && $this->item->syncRepertoryItems($this->repertory_item_id);
        } catch (\Throwable $e) {
            Log::inst()->error("商品同步失败：{$e->getMessage()}");
        }
    }

    /**
     * @return HasOne
     */
    public function repertoryItem(): HasOne
    {
        return $this->hasOne(RepertoryItem::class, "id", "repertory_item_id");
    }

    /**
     * @return HasMany
     */
    public function wholesale(): HasMany
    {
        return $this->hasMany(RepertoryItemSkuWholesale::class, "sku_id", "id");
    }


}