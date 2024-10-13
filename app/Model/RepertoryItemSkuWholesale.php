<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $sku_id
 * @property int $quantity
 * @property float $stock_price
 * @property string $create_time
 */
class RepertoryItemSkuWholesale extends Model
{
    use \Kernel\Component\Inject;

    /**
     * @var string|null
     */
    protected ?string $table = "repertory_item_sku_wholesale";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'sku_id' => 'integer', 'user_id' => 'integer', 'quantity' => 'integer'];


    #[Inject]
    protected \App\Service\User\Item $item;


    /**
     * @return HasOne
     */
    public function sku(): HasOne
    {
        return $this->hasOne(RepertoryItemSku::class, "id", "sku_id");
    }

    /**
     * 同步所有对接站点
     * @return void
     */
    public function saved(): void
    {
        $this->sku && $this->item->syncRepertoryItems($this->sku->repertory_item_id);
    }

}