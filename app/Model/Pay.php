<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;
use Kernel\Util\Date;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $icon
 * @property string $code
 * @property string $create_time
 * @property int $pay_config_id
 * @property int $sort
 * @property int $equipment
 * @property int $status
 * @property int $pid
 * @property array|string $scope
 * @property int $substation_status
 * @property float $substation_fee
 * @property float $api_fee
 */
class Pay extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "pay";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'pay_config_id' => 'integer', 'sort' => 'integer', 'substation_status' => 'integer', 'equipment' => 'integer', 'status' => 'integer', 'pid' => 'integer', 'scope' => 'json'];


    public function saved(): void
    {
        if (!$this->pid) {
            Pay::query()->where("pid", $this->id)->update(["scope" => is_array($this->scope) ? json_encode($this->scope) : "[]"]);
        }
    }


    /**
     * @return HasOne
     */
    public function config(): HasOne
    {
        return $this->hasOne(PluginConfig::class, "id", "pay_config_id");
    }


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id")->select(["id", "username", "avatar"]);
    }

    /**
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(Pay::class, "id", "pid");
    }


    /**
     * 总订单
     * @return HasMany
     */
    public function order(): HasMany
    {
        return $this->hasMany(PayOrder::class, "pay_id", "id");
    }

    /**
     * 已付款订单
     * @return HasMany
     */
    public function paidOrder(): HasMany
    {
        return $this->order()->where("status", 2);
    }

    /**
     * 今日订单
     * @return HasMany
     */
    public function todayOrder(): HasMany
    {
        return $this->paidOrder()->whereBetween("create_time", [Date::calcDay(), Date::calcDay(1)]);
    }

    /**
     * 昨日订单
     * @return HasMany
     */
    public function yesterdayOrder(): HasMany
    {
        return $this->paidOrder()->whereBetween("create_time", [Date::calcDay(-1), Date::calcDay()]);
    }

    /**
     * 本周内的订单
     * @return HasMany
     */
    public function weekdayOrder(): HasMany
    {
        return $this->paidOrder()->whereBetween("create_time", [Date::getDateByWeekday(1) . " 00:00:00", Date::getDateByWeekday(7) . " 23:59:59"]);
    }

    /**
     * 本月内的订单
     * @return HasMany
     */
    public function monthOrder(): HasMany
    {
        return $this->paidOrder()->whereBetween("create_time", [Date::getFirstDayOfMonth() . " 00:00:00", Date::getLastDayOfMonth() . " 23:59:59"]);
    }

    /**
     * 上月的订单
     * @return HasMany
     */
    public function lastMonthOrder(): HasMany
    {
        return $this->paidOrder()->whereBetween("create_time", [Date::getFirstDayOfLastMonth() . " 00:00:00", Date::getLastDayOfLastMonth() . " 23:59:59"]);
    }
}