<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;
use Kernel\Util\Date;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $bank_id
 * @property string $card_no
 * @property string $card_image
 * @property string $card_image_hash
 * @property integer $status
 * @property string $create_time
 */
class UserBankCard extends Model
{
    protected ?string $table = 'user_bank_card';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'bank_id' => 'integer', 'status' => 'integer'];


    /**
     * @return HasOne
     */
    public function bank(): HasOne
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select(['id', 'username', 'avatar']);
    }

    /**
     * @return HasMany
     */
    public function withdraw(): HasMany
    {
        return $this->hasMany(UserWithdraw::class, 'card_id', 'id');
    }


    /**
     * @return HasMany
     */
    public function totalWithdraw(): HasMany
    {
        return $this->withdraw()->where("status", "=", 1);
    }


    /**
     * 今日订单
     * @return HasMany
     */
    public function todayWithdraw(): HasMany
    {
        return $this->withdraw()->where("status", "=", 1)->whereBetween("create_time", [Date::calcDay(), Date::calcDay(1)]);
    }

    /**
     * 昨日订单
     * @return HasMany
     */
    public function yesterdayWithdraw(): HasMany
    {
        return $this->withdraw()->where("status", "=", 1)->whereBetween("create_time", [Date::calcDay(-1), Date::calcDay()]);
    }

    /**
     * 本周内的订单
     * @return HasMany
     */
    public function weekdayWithdraw(): HasMany
    {
        return $this->withdraw()->where("status", "=", 1)->whereBetween("create_time", [Date::getDateByWeekday(1) . " 00:00:00", Date::getDateByWeekday(7) . " 23:59:59"]);
    }

    /**
     * 本月内的订单
     * @return HasMany
     */
    public function monthWithdraw(): HasMany
    {
        return $this->withdraw()->where("status", "=", 1)->whereBetween("create_time", [Date::getFirstDayOfMonth() . " 00:00:00", Date::getLastDayOfMonth() . " 23:59:59"]);
    }

    /**
     * 上月的订单
     * @return HasMany
     */
    public function lastMonthWithdraw(): HasMany
    {
        return $this->withdraw()->where("status", "=", 1)->whereBetween("create_time", [Date::getFirstDayOfLastMonth() . " 00:00:00", Date::getLastDayOfLastMonth() . " 23:59:59"]);
    }
}