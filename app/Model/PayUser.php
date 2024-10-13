<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $pay_id
 * @property string $temp_id
 * @property float $fee
 * @property integer $status
 * @property string $create_time
 */
class PayUser extends Model
{
    protected ?string $table = 'pay_user';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'pay_id' => 'integer', 'fee' => 'float', 'status' => 'integer'];
}