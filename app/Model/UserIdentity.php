<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $id_card
 * @property integer $type
 * @property string $front_image
 * @property string $back_image
 * @property integer $status
 * @property string $create_time
 * @property string $review_time
 */
class UserIdentity extends Model
{
    protected ?string $table = 'user_identity';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'type' => 'integer', 'status' => 'integer'];


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}