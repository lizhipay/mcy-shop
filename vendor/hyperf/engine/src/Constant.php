<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Engine;

use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Coroutine\Server;

class Constant
{
    public const ENGINE = 'Swoole';

    public static function isCoroutineServer($server): bool
    {
        return $server instanceof Server || $server instanceof HttpServer;
    }
}
