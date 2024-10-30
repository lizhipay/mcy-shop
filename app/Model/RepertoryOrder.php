<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Component\Inject;
use Kernel\Database\Model;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;

/**
 * @property int $id
 * @property int $user_id
 * @property int $customer_id
 * @property string $trade_no
 * @property string $item_trade_no
 * @property string $main_trade_no
 * @property float $amount
 * @property int $repertory_item_id
 * @property int $repertory_item_sku_id
 * @property int $quantity
 * @property string $trade_time
 * @property string $trade_ip
 * @property int $status
 * @property float $supply_profit
 * @property float $office_profit
 * @property string $widget
 * @property float $item_cost
 * @property string $contents
 */
class RepertoryOrder extends Model
{

    use Inject;

    #[\Kernel\Annotation\Inject]
    private \App\Service\User\Order $order;

    /**
     * @var string|null
     */
    protected ?string $table = "repertory_order";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'customer_id' => 'integer', 'repertory_item_id' => 'integer', 'repertory_item_sku_id' => 'integer', 'quantity' => 'integer', 'status' => 'integer'];


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function saved(): void
    {
        $this->id && $this->order->syncDeliver($this->item_trade_no, $this->contents, $this->status == 1 ? 1 : 0);
        Plugin::inst()->unsafeHook(Usr::inst()->userToEnv($this->user_id), Point::MODEL_REPERTORY_ORDER_SAVE, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $this);
    }


    /**
     * @return HasOne
     */
    public function supplier(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id")->select(["id", "username", "avatar"]);
    }


    /**
     * @return HasOne
     */
    public function customer(): HasOne
    {
        return $this->hasOne(User::class, "id", "customer_id")->select(["id", "username", "avatar"]);
    }


    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(RepertoryItem::class, "id", "repertory_item_id")->select(["id", "name", "picture_thumb_url", "plugin"]);
    }


    /**
     * @return HasOne
     */
    public function sku(): HasOne
    {
        return $this->hasOne(RepertoryItemSku::class, "id", "repertory_item_sku_id")->select(["id", "name", "picture_thumb_url"]);
    }


    /**
     * @return HasMany
     */
    public function commission(): HasMany
    {
        return $this->hasMany(RepertoryOrderCommission::class, "order_id", "id")->with(["user", "parent"]);
    }

}