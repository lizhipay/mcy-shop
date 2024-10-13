<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;
use Kernel\Util\Date;

/**
 * @property int $id
 * @property int $user_id
 * @property int $customer_id
 * @property int $pay_id
 * @property int $order_id
 * @property float $order_amount
 * @property float $trade_amount
 * @property int $balance_status
 * @property float $balance_amount
 * @property int $status
 * @property string $pay_url
 * @property int $render_mode
 * @property string $create_time
 * @property string $pay_time
 * @property string $timeout
 * @property string $fee
 * @property string $api_fee;
 */
class PayOrder extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "pay_order";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'pay_id' => 'integer', 'order_id' => 'integer', 'status' => 'integer', 'render_mode' => 'integer', 'balance_status' => 'integer', 'user_id' => 'integer', 'customer_id' => 'integer'];


    /**
     * @return HasOne|null
     */
    public function pay(): ?HasOne
    {
        return $this->hasOne(Pay::class, "id", "pay_id");
    }


    /**
     * @return HasOne|null
     */
    public function option(): ?HasOne
    {
        return $this->hasOne(PayOrderOption::class, "pay_order_id", "id");
    }

    /**
     * @return HasOne|null
     */
    public function customer(): ?HasOne
    {
        return $this->hasOne(User::class, "id", "customer_id");
    }

    /**
     * @return HasOne|null
     */
    public function user(): ?HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    /**
     * @return HasOne|null
     */
    public function order(): ?HasOne
    {
        return $this->hasOne(Order::class, "id", "order_id");
    }

    /**
     * @param array $option
     * @return void
     */
    public function setOption(array $option): void
    {
        $payOrderOption = new PayOrderOption();
        $payOrderOption->pay_order_id = $this->attributes['id'];
        $payOrderOption->option = $option;
        $payOrderOption->create_date = Date::current();
        $payOrderOption->save();
    }
}