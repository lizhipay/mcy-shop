<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property float $before_balance
 * @property float $after_balance
 * @property int $type
 * @property string $create_time
 * @property string $update_time
 * @property int $status
 * @property int $action
 * @property string $trade_no
 * @property int $is_withdraw
 * @property string $unfreeze_time
 * @property string $remark
 */
class UserBill extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "user_bill";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'status' => 'integer', 'action' => 'integer', 'user_id' => 'integer', 'type' => 'integer', 'is_withdraw' => 'integer'];


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select(['id', 'username', 'avatar']);
    }
}