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

use Hyperf\Engine\Contract\Http\Writable;
use Swoole\Http\Response;

class WritableConnection implements Writable
{
    public function __construct(protected Response $response)
    {
    }

    public function write(string $data): bool
    {
        return $this->response->write($data);
    }

    /**
     * @return Response
     */
    public function getSocket(): mixed
    {
        return $this->response;
    }

    public function end(): bool
    {
        return $this->response->end();
    }
}
