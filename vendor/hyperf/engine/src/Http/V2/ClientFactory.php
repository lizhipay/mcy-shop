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

namespace Hyperf\Engine\Http\V2;

use Hyperf\Engine\Contract\Http\V2\ClientFactoryInterface;

class ClientFactory implements ClientFactoryInterface
{
    public function make(string $host, int $port = 80, bool $ssl = false)
    {
        return new Client($host, $port, $ssl);
    }
}
