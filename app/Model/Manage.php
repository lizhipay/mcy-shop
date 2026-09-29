<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\HasMany as HasManyAlias;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string $security_password
 * @property string $nickname
 * @property string $salt
 * @property string $avatar
 * @property int $status
 * @property int $type
 * @property string $create_time
 * @property string $login_time
 * @property string $last_login_time
 * @property string $login_ip
 * @property string $last_login_ip
 * @property string $login_ua
 * @property string $last_login_ua
 * @property string $client_token
 * @property string $note
 * @property int $login_status
 */
class Manage extends Model
{
    /**
     * @var ?string
     */
    protected ?string $table = "manage";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'status' => 'integer', 'type' => 'integer', 'login_status' => 'integer'];

    /**
     * 密码/盐/支付密码为 JWT 签名密钥与敏感凭据，禁止随 toArray/JSON 输出（比照 User model），
     * 杜绝子管理员经 manage/get 读到其它管理员的凭据后离线破解或伪造 token
     * @var array
     */
    protected array $hidden = ['password', 'salt', 'security_password'];


    /**
     * @return BelongsToMany
     */
    public function role(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, "manage_role", "manage_id", "role_id");
    }

    /**
     * @return HasManyAlias
     */
    public function log(): HasManyAlias
    {
        return $this->hasMany(ManageLog::class, "manage_id", 'id');
    }
}