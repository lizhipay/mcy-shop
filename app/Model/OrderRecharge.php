<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property string $trade_no
 * @property float $amount
 * @property integer $status
 * @property string $create_time
 * @property string $recharge_time
 */
class OrderRecharge extends Model
{
    protected ?string $table = 'order_recharge';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'order_id' => 'integer', 'amount' => 'float', 'status' => 'integer'];
}