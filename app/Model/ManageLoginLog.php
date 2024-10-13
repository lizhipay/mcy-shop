<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $manage_id
 * @property string $create_time
 * @property string $ip
 * @property string $ua
 * @property integer $is_dangerous
 */
class ManageLoginLog extends Model
{
    protected ?string $table = 'manage_login_log';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'manage_id' => 'integer', 'is_dangerous' => 'integer'];
}