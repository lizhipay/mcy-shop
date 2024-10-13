<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $pid
 * @property string $route
 * @property int $type
 * @property int $rank
 * @property string $icon
 * @property string $plugin
 */
class Permission extends Model
{

    /**
     * @var ?string
     */
    protected ?string $table = "permission";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'pid' => 'integer', 'type' => 'integer', 'rank' => 'integer'];

    /**
     * @var array
     */
    private static array $data = [];

    /**
     * @param string $route
     * @return bool
     */
    public static function isRegister(string $route): bool
    {
        if (isset(self::$data [$route])) {
            return self::$data[$route];
        }
        $exists = Permission::where("route", $route)->exists();
        self::$data[$route] = $exists;
        return $exists;
    }
}