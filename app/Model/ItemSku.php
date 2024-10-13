<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $repertory_item_sku_id
 * @property int $user_id
 * @property int $item_id
 * @property string $name
 * @property float $price
 * @property float $stock_price
 * @property float $dividend_amount
 * @property int $sort
 * @property string $create_time
 * @property string $picture_url
 * @property string $picture_thumb_url
 * @property int $private_display
 */
class ItemSku extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "item_sku";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'repertory_item_sku_id' => 'integer', 'user_id' => 'integer', 'item_id' => 'integer', 'sort' => 'integer', 'private_display' => 'integer'];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    /**
     * @return HasOne
     */
    public function repertoryItemSku(): HasOne
    {
        return $this->hasOne(RepertoryItemSku::class, "id", "repertory_item_sku_id");
    }


    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(Item::class, "id", "item_id");
    }
}