<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $repertory_item_sku_wholesale_id
 * @property int $user_id
 * @property int $sku_id
 * @property int $quantity
 * @property float $price
 * @property float $stock_price
 * @property string $create_time
 * @property float $dividend_amount
 */
class ItemSkuWholesale extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "item_sku_wholesale";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'repertory_item_sku_wholesale_id' => 'integer', 'user_id' => 'integer', 'sku_id' => 'integer', 'quantity' => 'integer'];
}