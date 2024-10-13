<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $wholesale_id
 * @property int $user_id
 * @property int $customer_id
 * @property float $stock_price
 * @property int $status
 * @property string $create_time
 */
class RepertoryItemSkuWholesaleUser extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "repertory_item_sku_wholesale_user";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'wholesale_id' => 'integer', 'user_id' => 'integer', 'customer_id' => 'integer', 'status' => 'integer'];
}  