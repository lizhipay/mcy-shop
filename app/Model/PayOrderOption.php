<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $pay_order_id
 * @property array $option
 * @property string $create_date
 */
class PayOrderOption extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "pay_order_option";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'pay_order_id' => 'integer', 'option' => 'json']; 
}