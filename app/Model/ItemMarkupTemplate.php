<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Annotation\Inject;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $drift_model
 * @property float $drift_value
 * @property float $drift_base_amount
 * @property int $sync_amount
 * @property int $sync_name
 * @property int $sync_introduce
 * @property int $sync_picture
 * @property int $sync_sku_name
 * @property int $sync_sku_picture
 * @property string $keep_decimals
 */
class ItemMarkupTemplate extends Model
{
    use \Kernel\Component\Inject;

    /**
     * @var string|null
     */
    protected ?string $table = "item_markup_template";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'drift_model' => 'integer',
        'sync_name' => 'integer',
        'sync_introduce' => 'integer',
        'sync_picture' => 'integer',
        'sync_sku_name' => 'integer',
        'sync_sku_picture' => 'integer',
        'sync_amount' => 'integer'
    ];

    #[Inject]
    private \App\Service\User\Item $item;

    /**
     * 同步所有模板下的商品
     * @return void
     */
    public function saved(): void
    {
        $this->id && $this->item->syncRepertoryItemForMarkupTemplate($this->id);
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
}