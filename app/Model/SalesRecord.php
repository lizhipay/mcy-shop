<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $salesman
 * @property string $product_name
 * @property float $order_amount
 * @property string $remark
 * @property string $create_time
 * @property int $status
 */
class SalesRecord extends Model
{
    protected ?string $table = 'sales_record';
    protected bool $timestamps = false;
}
