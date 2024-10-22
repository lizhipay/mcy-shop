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

use Hyperf\Engine\Contract\ChannelInterface;
use Hyperf\Engine\Exception\RuntimeException;

/**
 * @template TValue of mixed
 * @implements ChannelInterface
 */
class Channel extends \Swoole\Coroutine\Channel implements ChannelInterface
{
    protected bool $closed = false;

    /**
     * @param TValue $data
     * @param float|int $timeout seconds [optional] = -1
     */
    public function push(mixed $data, float $timeout = -1): bool
    {
        return parent::push($data, $timeout);
    }

    /**
     * @param float $timeout seconds [optional] = -1
     * @return false|TValue when pop failed, return false
     */
    public function pop(float $timeout = -1): mixed
    {
        return parent::pop($timeout);
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getLength(): int
    {
        return $this->length();
    }

    public function isAvailable(): bool
    {
        return ! $this->isClosing();
    }

    public function close(): bool
    {
        $this->closed = true;
        return parent::close();
    }

    public function hasProducers(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function hasConsumers(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function isReadable(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function isWritable(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function isClosing(): bool
    {
        return $this->closed || $this->errCode === SWOOLE_CHANNEL_CLOSED;
    }

    public function isTimeout(): bool
    {
        return ! $this->closed && $this->errCode === SWOOLE_CHANNEL_TIMEOUT;
    }
}
