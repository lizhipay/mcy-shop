<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property int $pid
 * @property float $amount
 * @property string $trade_no
 */
class RepertoryOrderCommission extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "repertory_order_commission";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'order_id' => 'integer', 'user_id' => 'integer', 'pid' => 'integer'];


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id")->select(["id", "username", "avatar"]);
    }


    /**
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(User::class, "id", "pid")->select(["id", "username", "avatar"]);
    }
}