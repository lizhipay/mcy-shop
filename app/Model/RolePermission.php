<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 */
class RolePermission extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "role_permission";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer' , 'role_id' => 'integer' , 'permission_id' => 'integer'];
}