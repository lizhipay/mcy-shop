<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $item_id
 * @property int $sku_id
 * @property int $quantity
 * @property float $amount
 * @property float $dividend_amount
 * @property int $status
 * @property int $refund_mode
 * @property string $treasure
 * @property array $widget
 * @property string $contact
 * @property string $create_time
 * @property string $trade_no
 * @property string $auto_receipt_time
 * @property string $update_time
 */
class OrderItem extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "order_item";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'order_id' => 'integer', 'item_id' => 'integer', 'sku_id' => 'integer', 'quantity' => 'integer', 'status' => 'integer', 'refund_mode' => 'integer', 'widget' => 'json'];


    /**
     * @return HasOne|null
     */
    public function sku(): ?HasOne
    {
        return $this->hasOne(ItemSku::class, "id", "sku_id");
    }

    /**
     * @return HasOne|null
     */
    public function item(): ?HasOne
    {
        return $this->hasOne(Item::class, "id", "item_id");
    }

    /**
     * @return HasOne|null
     */
    public function order(): ?HasOne
    {
        return $this->hasOne(Order::class, "id", "order_id");
    }
}