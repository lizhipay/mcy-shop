<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $customer_id
 * @property string $client_id
 * @property string $create_time
 */
class Cart extends Model
{
    protected ?string $table = 'cart';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'customer_id' => 'integer'];
}