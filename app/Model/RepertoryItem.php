<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Database\Model;
use Kernel\Util\Date;

/**
 * @property int $id
 * @property int $user_id
 * @property int $repertory_category_id
 * @property string $name
 * @property string $introduce
 * @property string $picture_url
 * @property string $picture_thumb_url
 * @property int $status
 * @property string $create_time
 * @property int $sort
 * @property int $item_type
 * @property string $plugin
 * @property int $privacy
 * @property string $widget
 * @property string $attr
 * @property string $api_code
 * @property int $refund_mode
 * @property int $auto_receipt_time
 * @property int $ship_config_id
 * @property string $unique_id
 * @property string $markup
 * @property int $markup_mode
 * @property int $markup_template_id
 * @property array $version
 * @property string $update_time
 * @property string $plugin_data
 * @property int $exception_total
 * @property int $is_review
 */
class RepertoryItem extends Model
{

    use \Kernel\Component\Inject;

    /**
     * @var ?string
     */
    protected ?string $table = "repertory_item";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'markup_template_id' => 'integer', 'exception_total' => 'integer', 'is_review' => 'integer', 'markup_mode' => 'integer', 'markup' => 'json', 'version' => 'json', 'user_id' => 'integer', 'ship_config_id' => 'integer', 'refund_mode' => 'integer', 'money_freeze_time' => 'integer', 'repertory_category_id' => 'integer', 'status' => 'integer', 'sort' => 'integer', 'item_type' => 'integer', 'privacy' => 'integer'];


    #[Inject]
    protected \App\Service\User\Item $item;

    /**
     * 同步所有对接站点
     * @return void
     */
    public function saved(): void
    {
        $this->id && $this->item->syncRepertoryItems($this->id);
    }


    /**
     * @return HasMany
     */
    public function sku(): HasMany
    {
        return $this->hasMany(RepertoryItemSku::class, "repertory_item_id", "id");
    }

    /**
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(RepertoryCategory::class, "id", "repertory_category_id");
    }

    /**
     * 供货商
     * @return HasOne
     */
    public function supplier(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id")->select(["id", "username", "avatar"]);
    }

    /**
     * 总订单
     * @return HasMany
     */
    public function order(): HasMany
    {
        return $this->hasMany(RepertoryOrder::class, "repertory_item_id", "id");
    }

    /**
     * 用户引用的商品列表
     * @return HasMany
     */
    public function userItem(): HasMany
    {
        return $this->HasMany(Item::class, "repertory_item_id", "id");
    }

    /**
     * 今日订单
     * @return HasMany
     */
    public function todayOrder(): HasMany
    {
        return $this->order()->whereBetween("trade_time", [Date::calcDay(), Date::calcDay(1)]);
    }

    /**
     * 昨日订单
     * @return HasMany
     */
    public function yesterdayOrder(): HasMany
    {
        return $this->order()->whereBetween("trade_time", [Date::calcDay(-1), Date::calcDay()]);
    }

    /**
     * 本周内的订单
     * @return HasMany
     */
    public function weekdayOrder(): HasMany
    {
        return $this->order()->whereBetween("trade_time", [Date::getDateByWeekday(1) . " 00:00:00", Date::getDateByWeekday(7) . " 23:59:59"]);
    }

    /**
     * 本月内的订单
     * @return HasMany
     */
    public function monthOrder(): HasMany
    {
        return $this->order()->whereBetween("trade_time", [Date::getFirstDayOfMonth() . " 00:00:00", Date::getLastDayOfMonth() . " 23:59:59"]);
    }

    /**
     * 上月的订单
     * @return HasMany
     */
    public function lastMonthOrder(): HasMany
    {
        return $this->order()->whereBetween("trade_time", [Date::getFirstDayOfLastMonth() . " 00:00:00", Date::getLastDayOfLastMonth() . " 23:59:59"]);
    }
}