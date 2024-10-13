<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $level_id
 * @property int $sku_id
 * @property float $price
 * @property int $status
 * @property string $create_time
 * @property float $dividend_amount
 */
class ItemSkuLevel extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "item_sku_level";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer' , 'user_id' => 'integer' , 'level_id' => 'integer' , 'sku_id' => 'integer' ,   'status' => 'integer'];
}