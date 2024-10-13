<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $salt
 * @property string $app_key
 * @property string $avatar
 * @property int $integral
 * @property int $pid
 * @property int $status
 * @property string $note
 * @property float $balance
 * @property float $withdraw_amount
 * @property int $group_id
 * @property int $level_id
 * @property string $api_code
 * @property int $invite_id
 */
class User extends Model
{
    /**
     * @var ?string
     */
    protected ?string $table = "user";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'invite_id' => 'integer', 'integral' => 'integer', 'group_id' => 'integer', 'level_id' => 'integer', 'pid' => 'integer', 'status' => 'integer'];


    /**
     * @return HasOne
     */
    public function lifetime(): HasOne
    {
        return $this->hasOne(UserLifetime::class, "user_id", "id");
    }


    /**
     * 用户组
     * @return HasOne|null
     */
    public function group(): ?HasOne
    {
        return $this->hasOne(UserGroup::class, "id", "group_id");
    }

    /**
     * 会员等级
     * @return HasOne|null
     */
    public function level(): ?HasOne
    {
        return $this->hasOne(UserLevel::class, "id", "level_id");
    }


    /**
     * 获取用户下的SKU独立配置
     * @return HasOne
     */
    public function repertoryItemSkuUser(): HasOne
    {
        return $this->hasOne(RepertoryItemSkuUser::class, "customer_id", "id");
    }

    /**
     * 获取用户下的独立支付配置
     * @return HasOne
     */
    public function payUser(): HasOne
    {
        return $this->hasOne(PayUser::class, "user_id", "id");
    }

    /**
     * 获取顾客的批发密价
     * @return HasOne
     */
    public function repertoryItemSkuWholesaleUser(): HasOne
    {
        return $this->hasOne(RepertoryItemSkuWholesaleUser::class, "customer_id", "id");
    }

    /**
     * @return HasOne
     */
    public function itemSkuUser(): HasOne
    {
        return $this->hasOne(ItemSkuUser::class, "customer_id", "id");
    }


    /**
     * @return HasOne
     */
    public function itemSkuWholesaleUser(): HasOne
    {
        return $this->hasOne(ItemSkuWholesaleUser::class, "customer_id", "id");
    }


    /**
     * 上级
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(User::class, "id", "pid");
    }


    /**
     * @return HasOne
     */
    public function identity(): HasOne
    {
        return $this->hasOne(UserIdentity::class, "user_id", "id");
    }

    /**
     * @return HasMany
     */
    public function site(): HasMany
    {
        return $this->hasMany(Site::class, "user_id", "id");
    }
}