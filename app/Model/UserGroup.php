<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $icon
 * @property string $name
 * @property float $price
 * @property int $sort
 * @property int $is_merchant
 * @property int $is_supplier
 * @property int $tax_ratio
 * @property float $dividend_amount
 * @property int $is_upgradable
 */
class UserGroup extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "user_group";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'is_upgradable' => 'integer', 'price' => 'float', 'sort' => 'integer', 'is_supplier' => 'integer', 'is_merchant' => 'integer'];


    /**
     * @return HasMany
     */
    public function user(): HasMany
    {
        return $this->hasMany(User::class, "group_id", "id");
    }


    /**
     * 获取用户组下的SKU独立配置
     * @return HasOne
     */
    public function repertoryItemSkuGroup(): HasOne
    {
        return $this->hasOne(RepertoryItemSkuGroup::class, "group_id", "id");
    }

    /**
     * 获取用户组下的独立支付配置
     * @return HasOne
     */
    public function payGroup(): HasOne
    {
        return $this->hasOne(PayGroup::class, "group_id", "id");
    }

    /**
     * 获取用户组下的批发规则
     * @return HasOne
     */
    public function repertoryItemSkuWholesaleGroup(): HasOne
    {
        return $this->hasOne(RepertoryItemSkuWholesaleGroup::class, "group_id", "id");
    }
}