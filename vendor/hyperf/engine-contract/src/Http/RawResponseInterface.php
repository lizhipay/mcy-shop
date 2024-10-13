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

namespace Hyperf\Engine\Contract\Http;

interface RawResponseInterface
{
    public function getStatusCode(): int;

    /**
     * @return string[][]
     */
    public function getHeaders(): array;

    public function getBody(): string;

    public function getVersion(): string;
}
