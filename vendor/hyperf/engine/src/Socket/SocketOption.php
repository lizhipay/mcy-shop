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

namespace Hyperf\Engine\Socket;

use Hyperf\Engine\Contract\Socket\SocketOptionInterface;

class SocketOption implements SocketOptionInterface
{
    public function __construct(protected string $host, protected int $port, protected ?float $timeout = null, protected array $protocol = [])
    {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    public function getProtocol(): array
    {
        return $this->protocol;
    }
}
