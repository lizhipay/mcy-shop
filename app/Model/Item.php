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
 * @property int $repertory_item_id
 * @property int $category_id
 * @property string $name
 * @property string $introduce
 * @property string $picture_url
 * @property string $picture_thumb_url
 * @property int $status
 * @property string $create_time
 * @property int $sort
 * @property string $markup
 * @property int $markup_mode
 * @property int $markup_template_id
 * @property string $attr
 * @property int $recommend
 * @property string $dividend_amount
 */
class Item extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "item";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'markup_template_id' => 'integer', 'markup_mode' => 'integer', 'repertory_item_id' => 'integer', 'category_id' => 'integer', 'status' => 'integer', 'sort' => 'integer', 'recommend' => 'integer', 'markup' => 'json'];


    /**
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(Category::class, "id", "category_id")->select();
    }

    /**
     * @return HasMany
     */
    public function sku(): HasMany
    {
        return $this->hasMany(ItemSku::class, "item_id", "id");
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
    public function repertoryItem(): HasOne
    {
        return $this->hasOne(RepertoryItem::class, "id", "repertory_item_id");
    }


    /**
     * 总订单
     * @return HasMany
     */
    public function order(): HasMany
    {
        return $this->hasMany(OrderItem::class, "item_id", "id");
    }


    /**
     * 今日订单
     * @return HasMany
     */
    public function todayOrder(): HasMany
    {
        return $this->order()->where("status", "!=", 0)->whereBetween("create_time", [Date::calcDay(), Date::calcDay(1)]);
    }

    /**
     * 昨日订单
     * @return HasMany
     */
    public function yesterdayOrder(): HasMany
    {
        return $this->order()->where("status", "!=", 0)->whereBetween("create_time", [Date::calcDay(-1), Date::calcDay()]);
    }

    /**
     * 本周内的订单
     * @return HasMany
     */
    public function weekdayOrder(): HasMany
    {
        return $this->order()->where("status", "!=", 0)->whereBetween("create_time", [Date::getDateByWeekday(1) . " 00:00:00", Date::getDateByWeekday(7) . " 23:59:59"]);
    }

    /**
     * 本月内的订单
     * @return HasMany
     */
    public function monthOrder(): HasMany
    {
        return $this->order()->where("status", "!=", 0)->whereBetween("create_time", [Date::getFirstDayOfMonth() . " 00:00:00", Date::getLastDayOfMonth() . " 23:59:59"]);
    }

    /**
     * 上月的订单
     * @return HasMany
     */
    public function lastMonthOrder(): HasMany
    {
        return $this->order()->where("status", "!=", 0)->whereBetween("create_time", [Date::getFirstDayOfLastMonth() . " 00:00:00", Date::getLastDayOfLastMonth() . " 23:59:59"]);
    }
}