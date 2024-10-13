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

use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Socket\SocketFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                SocketFactoryInterface::class => SocketFactory::class,
            ],
        ];
    }
}
