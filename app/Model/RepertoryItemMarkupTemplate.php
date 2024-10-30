<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property integer $drift_model
 * @property float $drift_value
 * @property float $drift_base_amount
 * @property integer $sync_amount
 * @property integer $sync_name
 * @property integer $sync_introduce
 * @property integer $sync_picture
 * @property integer $sync_sku_name
 * @property integer $sync_sku_picture
 * @property integer $sync_remote_download
 * @property string $keep_decimals
 * @property string $exchange_rate
 * @property string $create_time
 */
class RepertoryItemMarkupTemplate extends Model
{
    protected ?string $table = 'repertory_item_markup_template';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'drift_model' => 'integer', 'drift_value' => 'float', 'drift_base_amount' => 'float', 'sync_amount' => 'integer', 'sync_name' => 'integer', 'sync_introduce' => 'integer', 'sync_picture' => 'integer', 'sync_sku_name' => 'integer', 'sync_sku_picture' => 'integer', 'sync_remote_download' => 'integer'];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
}