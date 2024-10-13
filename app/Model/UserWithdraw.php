<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property integer $card_id
 * @property float $amount
 * @property integer $status
 * @property string $handle_message
 * @property string $create_time
 * @property string $handle_time
 * @property string $trade_no
 */
class UserWithdraw extends Model
{
    protected ?string $table = 'user_withdraw';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'card_id' => 'integer', 'amount' => 'float', 'status' => 'integer'];


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function card(): HasOne
    {
        return $this->hasOne(UserBankCard::class, 'id', 'card_id')->with(['bank']);
    }
}