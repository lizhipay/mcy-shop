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

use Hyperf\Engine\Contract\Http\V2\ResponseInterface;

class Response implements ResponseInterface
{
    public function __construct(protected int $streamId, protected int $statusCode, protected array $headers, protected ?string $body)
    {
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }
}
