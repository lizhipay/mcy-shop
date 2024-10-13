<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $group_id
 * @property integer $pay_id
 * @property string $temp_id
 * @property float $fee
 * @property integer $status
 * @property string $create_time
 */
class PayGroup extends Model
{
    protected ?string $table = 'pay_group';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'group_id' => 'integer', 'pay_id' => 'integer', 'fee' => 'float', 'status' => 'integer'];
}