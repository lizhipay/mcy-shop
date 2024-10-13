<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $trade_no
 * @property int $user_id
 * @property int $customer_id
 * @property int $invite_id
 * @property float $total_amount
 * @property int $status
 * @property int $type
 * @property string $create_time
 * @property string $pay_time
 * @property string $client_id
 * @property string $create_ip
 * @property string $create_browser
 * @property string $create_device
 * @property string $option
 */
class Order extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "order";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'customer_id' => 'integer', 'invite_id' => 'integer', 'status' => 'integer', 'type' => 'integer'];


    /**
     * @return HasMany
     */
    public function item(): HasMany
    {
        return $this->hasMany(OrderItem::class, "order_id", "id");
    }

    /**
     * @return HasOne
     */
    public function customer(): HasOne
    {
        return $this->hasOne(User::class, "id", "customer_id");
    }

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
    public function invite(): HasOne
    {
        return $this->hasOne(User::class, "id", "invite_id");
    }

    /**
     * @return HasOne
     */
    public function payOrder(): HasOne
    {
        return $this->hasOne(PayOrder::class, "order_id", "id");
    }
}