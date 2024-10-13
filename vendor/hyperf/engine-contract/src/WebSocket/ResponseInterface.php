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

interface ResponseInterface
{
    public function push(FrameInterface $frame): bool;

    /**
     * Init fd by frame or request and so on,
     * Must be used in swoole process mode.
     */
    public function init(mixed $frame): static;

    public function getFd(): int;

    public function close(): bool;
}
