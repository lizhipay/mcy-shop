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

namespace Hyperf\Engine\Http;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Contract\Http\ServerFactoryInterface;
use Hyperf\Engine\Contract\Http\ServerInterface;

class ServerFactory implements ServerFactoryInterface
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function make(string $name, int $port = 0): ServerInterface
    {
        $server = new Server($this->logger);

        return $server->bind($name, $port);
    }
}
