<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $hash
 * @property string $type
 * @property string $path
 * @property string $create_time
 * @property string $note
 */
class Upload extends Model
{
    protected ?string $table = 'upload';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer'];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }
}