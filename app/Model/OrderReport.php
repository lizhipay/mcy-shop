<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $order_item_id
 * @property integer $supply_id
 * @property integer $merchant_id
 * @property integer $customer_id
 * @property integer $status
 * @property float $refund_amount
 * @property float $refund_merchant_amount
 * @property integer $type
 * @property integer $expect
 * @property integer $handle_type
 * @property string $create_time
 */
class OrderReport extends Model
{
    protected ?string $table = 'order_report';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'order_item_id' => 'integer', 'supply_id' => 'integer', 'merchant_id' => 'integer', 'customer_id' => 'integer', 'status' => 'integer', 'handle_type' => 'integer', 'refund_amount' => 'float', 'type' => 'integer', 'expect' => 'integer'];


    public function supply(): ?HasOne
    {
        return $this->hasOne(User::class, "id", "supply_id");
    }


    public function merchant(): ?HasOne
    {
        return $this->hasOne(User::class, "id", "merchant_id");
    }


    public function customer(): ?HasOne
    {
        return $this->hasOne(User::class, "id", "customer_id");
    }

    public function orderItem(): ?HasOne
    {
        return $this->hasOne(OrderItem::class, "id", "order_item_id");
    }

}