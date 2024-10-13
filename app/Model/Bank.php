<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property string $icon
 * @property string $name
 * @property string $code
 * @property integer $status
 */
class Bank extends Model
{
    protected ?string $table = 'bank';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'status' => 'integer'];
}