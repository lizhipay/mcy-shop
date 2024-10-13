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

use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Contract\Socket\SocketOptionInterface;
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Engine\Exception\SocketConnectException;
use Hyperf\Engine\Socket;

class SocketFactory implements SocketFactoryInterface
{
    public function make(SocketOptionInterface $option): SocketInterface
    {
        $socket = new Socket(AF_INET, SOCK_STREAM, 0);
        if ($protocol = $option->getProtocol()) {
            $socket->setProtocol($protocol);
        }

        if ($option->getTimeout() === null) {
            $res = $socket->connect($option->getHost(), $option->getPort());
        } else {
            $res = $socket->connect($option->getHost(), $option->getPort(), $option->getTimeout());
        }

        if (! $res) {
            throw new SocketConnectException($socket->errMsg, $socket->errCode);
        }

        return $socket;
    }
}
