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

use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Engine\Exception\SocketClosedException;
use Hyperf\Engine\Exception\SocketTimeoutException;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Socket;
use Throwable;

class SafeSocket implements SocketInterface
{
    protected Channel $channel;

    protected bool $loop = false;

    public function __construct(
        protected Socket $socket,
        int $capacity = 65535,
        protected bool $throw = true,
        protected ?LoggerInterface $logger = null
    ) {
        $this->channel = new Channel($capacity);
    }

    /**
     * @throws SocketTimeoutException when send data timeout
     * @throws SocketClosedException when the client is closed
     */
    public function sendAll(string $data, float $timeout = 0): false|int
    {
        $this->loop();

        $res = $this->channel->push([$data, $timeout], $timeout);
        if ($res === false) {
            if ($this->channel->isClosing()) {
                $this->throw && throw new SocketClosedException('The channel is closed.');
            }
            if ($this->channel->isTimeout()) {
                $this->throw && throw new SocketTimeoutException('The channel is full.');
            }

            return false;
        }
        return strlen($data);
    }

    /**
     * @throws SocketTimeoutException when send data timeout
     * @throws SocketClosedException when the client is closed
     */
    public function recvAll(int $length = 65536, float $timeout = 0): false|string
    {
        $res = $this->socket->recvAll($length, $timeout);
        if (! $res) {
            if ($this->socket->errCode === SOCKET_ETIMEDOUT) {
                $this->throw && throw new SocketTimeoutException('Recv timeout');
            }

            $this->throw && throw new SocketClosedException('The socket is closed.');
        }

        return $res;
    }

    /**
     * @throws SocketTimeoutException when send data timeout
     * @throws SocketClosedException when the client is closed
     */
    public function recvPacket(float $timeout = 0): false|string
    {
        $res = $this->socket->recvPacket($timeout);
        if (! $res) {
            if ($this->socket->errCode === SOCKET_ETIMEDOUT) {
                $this->throw && throw new SocketTimeoutException('Recv timeout');
            }

            $this->throw && throw new SocketClosedException('The socket is closed.');
        }

        return $res;
    }

    public function close(): bool
    {
        $this->channel->close();

        return $this->socket->close();
    }

    public function setLogger(?LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    protected function loop(): void
    {
        if ($this->loop) {
            return;
        }

        $this->loop = true;

        Coroutine::create(function () {
            try {
                while (true) {
                    $data = $this->channel->pop(-1);
                    if ($this->channel->isClosing()) {
                        return;
                    }

                    [$data, $timeout] = $data;

                    $this->socket->sendAll($data, $timeout);
                }
            } catch (Throwable $exception) {
                $this->logger?->critical((string) $exception);
            }
        });
    }
}
