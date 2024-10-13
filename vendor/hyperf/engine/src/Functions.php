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

use Hyperf\Engine\Contract\WebSocket\FrameInterface;

/**
 * @internal
 */
function swoole_get_flags_from_frame(FrameInterface $frame): int
{
    $flags = 0;
    if ($frame->getFin()) {
        $flags |= SWOOLE_WEBSOCKET_FLAG_FIN;
    }
    if ($frame->getRSV1()) {
        $flags |= SWOOLE_WEBSOCKET_FLAG_RSV1;
    }
    if ($frame->getRSV2()) {
        $flags |= SWOOLE_WEBSOCKET_FLAG_RSV2;
    }
    if ($frame->getRSV3()) {
        $flags |= SWOOLE_WEBSOCKET_FLAG_RSV3;
    }
    if ($frame->getMask()) {
        $flags |= SWOOLE_WEBSOCKET_FLAG_MASK;
    }

    return $flags;
}
