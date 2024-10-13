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

namespace Hyperf\Engine\Contract;

/**
 * @template TValue of mixed
 */
interface ChannelInterface
{
    /**
     * @param TValue $data
     * @param float|int $timeout seconds [optional] = -1
     */
    public function push(mixed $data, float $timeout = -1): bool;

    /**
     * @param float $timeout seconds [optional] = -1
     * @return false|TValue when pop failed, return false
     */
    public function pop(float $timeout = -1): mixed;

    /**
     * Swow: When the channel is closed, all the data in it will be destroyed.
     * Swoole: When the channel is closed, the data in it can still be popped out, but push behavior will no longer succeed.
     */
    public function close(): bool;

    public function getCapacity(): int;

    public function getLength(): int;

    public function isAvailable(): bool;

    public function hasProducers(): bool;

    public function hasConsumers(): bool;

    public function isEmpty(): bool;

    public function isFull(): bool;

    public function isReadable(): bool;

    public function isWritable(): bool;

    public function isClosing(): bool;

    public function isTimeout(): bool;
}
