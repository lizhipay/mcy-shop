<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $name
 * @property string $plugin
 * @property string $handle
 * @property array $config
 * @property string $create_time
 */
class PluginConfig extends Model
{
    protected ?string $table = 'plugin_config';
    public bool $timestamps = false;
    protected array $casts = ['id' => 'integer', 'user_id' => 'integer', 'config' => 'json'];
}