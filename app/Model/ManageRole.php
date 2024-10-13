<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $manage_id
 * @property int $role_id
 */
class ManageRole extends Model
{
    /**
     * @var ?string
     */
    protected ?string $table = "manage_role";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer' , 'manage_id' => 'integer' , 'role_id' => 'integer'];
}