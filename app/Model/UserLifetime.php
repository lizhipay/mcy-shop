<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property float $total_consumption_amount
 * @property float $total_recharge_amount
 * @property integer $total_referral_count
 * @property integer $favorite_item_id
 * @property integer $favorite_item_count
 * @property integer $total_login_count
 * @property float $total_profit_amount
 * @property float $total_withdraw_amount
 * @property integer $total_withdraw_count
 * @property integer $share_item_id
 * @property integer $share_item_count
 * @property string $last_consumption_time
 * @property string $last_login_time
 * @property string $last_active_time
 * @property string $register_time
 * @property string $register_ip
 * @property string $register_ua
 * @property integer $login_status
 */
class UserLifetime extends Model
{
    protected ?string $table = 'user_lifetime';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'total_consumption_amount' => 'float', 'total_recharge_amount' => 'float', 'total_referral_count' => 'integer', 'favorite_item_id' => 'integer', 'favorite_item_count' => 'integer', 'total_login_count' => 'integer', 'total_profit_amount' => 'float', 'total_withdraw_amount' => 'float', 'total_withdraw_count' => 'integer', 'share_item_id' => 'integer', 'share_item_count' => 'integer', 'login_status' => 'integer'];


    /**
     * @return HasOne
     */
    public function favoriteItem(): HasOne
    {
        return $this->hasOne(Item::class, 'id', 'favorite_item_id')->select(['id', 'name', 'picture_url', 'picture_thumb_url']);
    }

    /**
     * @return HasOne
     */
    public function shareItem(): HasOne
    {
        return $this->hasOne(Item::class, 'id', 'share_item_id')->select(['id', 'name', 'picture_url', 'picture_thumb_url']);
    }


}