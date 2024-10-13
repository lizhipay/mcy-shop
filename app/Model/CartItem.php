<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $cart_id
 * @property integer $quantity
 * @property integer $sku_id
 * @property float $amount
 * @property float $price
 * @property string $create_time
 * @property string $update_time
 * @property array $option
 */
class CartItem extends Model
{
    protected ?string $table = 'cart_item';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'cart_id' => 'integer', 'quantity' => 'integer', 'sku_id' => 'integer', 'option' => 'json'];


    /**
     * @return HasOne
     */
    public function sku(): HasOne
    {
        return $this->hasOne(ItemSku::class, "id", "sku_id");
    }
}