<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Events\Deleting;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $create_time
 * @property int $status
 */
class Role extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "role";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'status' => 'integer'];

    /**
     * @return BelongsToMany
     */
    public function permission(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, "role_permission", "role_id", "permission_id");
    }
}