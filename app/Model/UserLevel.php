<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $icon
 * @property string $name
 * @property string $upgrade_requirements
 * @property string $privilege_introduce
 * @property string $privilege_content
 * @property string $create_time
 * @property float $upgrade_price
 * @property int $sort
 * @property int $is_upgradable
 */
class UserLevel extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "user_level";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'is_upgradable' => 'integer', 'sort' => 'integer'];


    /**
     * @return hasOne
     */
    public function itemSkuLevel(): hasOne
    {
        return $this->hasOne(ItemSkuLevel::class, "level_id", "id");
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id")->select(["id", "username", "avatar", "group_id", "level_id"]);
    }

    /**
     * @return HasMany
     */
    public function member(): HasMany
    {
        return $this->hasMany(User::class, "level_id", "id")->select(["id", "username", "avatar", "group_id", "level_id"]);
    }

    /**
     * 获取会员等级下的批发规则
     * @return HasOne
     */
    public function itemSkuWholesaleLevel(): HasOne
    {
        return $this->hasOne(ItemSkuWholesaleLevel::class, "level_id", "id");
    }

}