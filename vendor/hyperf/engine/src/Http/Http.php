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

use Hyperf\Engine\Contract\Http\Http as HttpContract;
use Stringable;

class Http implements HttpContract
{
    public static function packRequest(string $method, string|Stringable $path, array $headers = [], string|Stringable $body = '', string $protocolVersion = HttpContract::DEFAULT_PROTOCOL_VERSION): string
    {
        $headerString = '';
        foreach ($headers as $key => $values) {
            foreach ((array) $values as $value) {
                $headerString .= sprintf("%s: %s\r\n", $key, $value);
            }
        }

        return sprintf(
            "%s %s HTTP/%s\r\n%s\r\n%s",
            $method,
            $path,
            $protocolVersion,
            $headerString,
            $body
        );
    }

    public static function packResponse(int $statusCode, string $reasonPhrase = '', array $headers = [], string|Stringable $body = '', string $protocolVersion = HttpContract::DEFAULT_PROTOCOL_VERSION): string
    {
        $headerString = '';
        foreach ($headers as $key => $values) {
            foreach ((array) $values as $value) {
                $headerString .= sprintf("%s: %s\r\n", $key, $value);
            }
        }
        return sprintf(
            "HTTP/%s %s %s\r\n%s\r\n%s",
            $protocolVersion,
            $statusCode,
            $reasonPhrase,
            $headerString,
            $body
        );
    }
}
