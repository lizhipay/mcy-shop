<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Context\Interface\Request;
use Kernel\Database\Model;
use Kernel\Util\Context;
use Kernel\Util\Date;

/**
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property string $create_time
 * @property string $create_ip
 * @property string $create_ua
 */
class UserLog extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "user_log";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer'];


    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }


    /**
     * 添加日志
     * @param string $content
     * @return void
     */
    public static function add(string $content): void
    {

        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);

        /**
         * @var Manage $manage
         */
        $user = Context::get(User::class);

        $log = new self();
        $log->user_id = $user->id;
        $log->content = $content;
        $log->create_time = Date::current();
        $log->create_ip = $request->clientIp();
        $log->ua = (string)$request->header("UserAgent");
        $log->save();
    }
}