<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Hyperf\Database\Model\Relations\HasMany;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $sort
 * @property string $create_time
 * @property string $icon
 * @property int $status
 * @property int $pid
 */
class Category extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "category";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'sort' => 'integer', 'status' => 'integer', 'pid' => 'integer'];


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    /**
     * @return HasMany
     */
    public function item(): HasMany
    {
        return $this->hasMany(Item::class, "category_id", "id");
    }
}