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

use Hyperf\Engine\Contract\DefaultOptionInterface;
use Hyperf\Engine\Exception\RuntimeException;

class DefaultOption implements DefaultOptionInterface
{
    public static function hookFlags(): int
    {
        if (! defined('SWOOLE_HOOK_ALL')) {
            throw new RuntimeException('The ext-swoole is required.');
        }

        return SWOOLE_HOOK_ALL;
    }
}
