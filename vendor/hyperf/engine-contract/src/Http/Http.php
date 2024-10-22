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

use Stringable;

interface Http
{
    public const DEFAULT_PROTOCOL_VERSION = '1.1';

    public static function packRequest(string $method, string|Stringable $path, array $headers = [], string|Stringable $body = '', string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION): string;

    public static function packResponse(int $statusCode, string $reasonPhrase = '', array $headers = [], string|Stringable $body = '', string $protocolVersion = self::DEFAULT_PROTOCOL_VERSION): string;
}
