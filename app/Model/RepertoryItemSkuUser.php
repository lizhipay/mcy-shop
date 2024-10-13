<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $customer_id
 * @property int $sku_id
 * @property float $stock_price
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
 * @property int $status
 * @property string $temp_id
 * @property string $create_time
 */
class RepertoryItemSkuUser extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "repertory_item_sku_user";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'market_control_only_num' => 'integer', 'market_control_max_num' => 'integer', 'market_control_min_num' => 'integer', 'customer_id' => 'integer', 'sku_id' => 'integer', 'status' => 'integer', 'market_control_status' => 'integer'];
}