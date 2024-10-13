<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Container\Memory;
use Kernel\Context\Interface\Request;
use Kernel\Database\Model;
use Kernel\Exception\NotFoundException;
use Kernel\Util\Context;

/**
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property string $title
 * @property string $value
 * @property string $icon
 * @property string $bg_url
 */
class Config extends Model
{
    /**
     * @var ?string
     */
    protected ?string $table = "config";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer'];


    /**
     * @param string $key
     * @return array
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public static function user(string $key): array
    {
        /**
         * @var User $user
         */
        $host = Context::get(Request::class)->header("Host");
        $user = Site::getUser((string)$host);
        if (!$user) {
            return [];
        }
        $cacheKey = "config_find_sql_{$key}_{$user->id}";

        if (Memory::instance()->has($cacheKey)) {
            return Memory::instance()->get($cacheKey);
        }

        $config = self::where("key", $key)->where("user_id", $user->id)->first();
        if (!$config) {
            return [];
        }

        $cfg = (array)json_decode((string)$config->value, true);
        Memory::instance()->set($cacheKey, $cfg);
        return $cfg;
    }

    /**
     * @param string $key
     * @return array
     * @throws \ReflectionException
     */
    public static function main(string $key): array
    {
        $cacheKey = "config_find_sql_" . $key;
        if (Memory::instance()->has($cacheKey)) {
            return Memory::instance()->get($cacheKey);
        }
        $cfg = [];
        $config = self::where("key", $key)->whereNull("user_id")->first();;
        if ($config) {
            $cfg = (array)json_decode((string)$config->value, true);
            Memory::instance()->set($cacheKey, $cfg);
        }
        return $cfg;
    }

    /**
     * @param string $key
     * @return array
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public static function auto(string $key): array
    {
        $config = self::user($key);
        if (empty($config)) {
            $config = self::main($key);
        }
        return $config;
    }


    /**
     * @param string $key
     * @param array $value
     * @param bool $cover
     * @return void
     */
    public static function set(string $key, array $value, bool $cover = false): void
    {
        $config = self::where("key", $key)->first();
        if (!$config) {
            $config = new self();
            $config->key = $key;
            $config->value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $config->save();
            return;
        }

        if (!$cover) {
            $cfg = (array)json_decode((string)$config->value, true);
            foreach ($value as $key => $val) {
                $cfg[$key] = $val;
            }
        } else {
            $cfg = $value;
        }

        $config->value = json_encode($cfg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $config->save();
    }
}