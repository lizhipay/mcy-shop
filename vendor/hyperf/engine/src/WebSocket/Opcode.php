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

namespace Hyperf\Engine\WebSocket;

class Opcode
{
    public const CONTINUATION = 0;

    public const TEXT = 1;

    public const BINARY = 2;

    public const CLOSE = 8;

    public const PING = 9;

    public const PONG = 10;
}
