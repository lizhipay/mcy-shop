<?php
declare(strict_types=1);

namespace App\Model;

use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Container\Memory;
use Kernel\Database\Model;
use Kernel\Exception\NotFoundException;
use Kernel\Util\Url;

/**
 * @property int $id
 * @property int $user_id
 * @property string $host
 * @property string $create_time
 * @property int $type
 * @property string $ssl_expire_time
 * @property string $ssl_issuer
 * @property string $ssl_domain
 * @property int $status
 */
class Site extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "site";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'type' => 'integer', 'status' => 'integer'];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, "id", "user_id");
    }


    /**
     * @param string $host
     * @return User|null
     * @throws NotFoundException
     */
    public static function getUser(string $host): ?User
    {
        $key = "site_host_find_sql_" . $host;
        if (Memory::instance()->has($key)) {
            return Memory::instance()->get($key);
        }
        /**
         * @var Site $site
         */
        $site = Site::query()->with(["user"])->where("host", $host)->orWhere("host", Url::getWildcard($host))->first();

        if (!$site || !$site->user) {
            return null;
        }

        if ($site->status != 1) {
            throw new NotFoundException("此站点已被关闭");
        }

        Memory::instance()->set($key, $site->user);
        return $site->user;
    }
}