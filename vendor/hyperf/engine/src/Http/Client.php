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

use Hyperf\Engine\Contract\Http\ClientInterface;
use Hyperf\Engine\Exception\HttpClientException;
use Swoole\Coroutine\Http\Client as HttpClient;

class Client extends HttpClient implements ClientInterface
{
    public function set(array $settings): bool
    {
        return parent::set($settings);
    }

    /**
     * @param string[][] $headers
     */
    public function request(string $method = 'GET', string $path = '/', array $headers = [], string $contents = '', string $version = '1.1'): RawResponse
    {
        $this->setMethod($method);
        $this->setData($contents);
        $this->setHeaders($this->encodeHeaders($headers));
        $this->execute($path);
        if ($this->errCode !== 0) {
            throw new HttpClientException($this->errMsg, $this->errCode);
        }
        return new RawResponse(
            $this->statusCode,
            $this->decodeHeaders($this->headers ?? []),
            $this->body,
            $version
        );
    }

    /**
     * @param string[] $headers
     * @return string[][]
     */
    private function decodeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $name => $header) {
            // The key of header is lower case.
            $result[$name][] = $header;
        }
        if ($this->set_cookie_headers) {
            $result['set-cookie'] = $this->set_cookie_headers;
        }
        return $result;
    }

    /**
     * Swoole engine not support two-dimensional array.
     * @param string[][] $headers
     * @return string[]
     */
    private function encodeHeaders(array $headers): array
    {
        $result = [];
        foreach ($headers as $name => $value) {
            $result[$name] = is_array($value) ? implode(',', $value) : $value;
        }

        return $result;
    }
}
