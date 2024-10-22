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

namespace Hyperf\Engine\Contract\WebSocket;

interface WebSocketInterface
{
    public const ON_MESSAGE = 'message';

    public const ON_CLOSE = 'close';

    public function on(string $event, callable $callback): void;

    public function start(): void;
}
