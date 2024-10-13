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

use Hyperf\Engine\Contract\Http\V2\ClientInterface;
use Hyperf\Engine\Contract\Http\V2\RequestInterface;
use Hyperf\Engine\Contract\Http\V2\ResponseInterface;
use Hyperf\Engine\Exception\HttpClientException;
use Swoole\Coroutine\Http2\Client as HTTP2Client;
use Swoole\Http2\Request as SwRequest;
use Swoole\Http2\Response as SwResponse;

class Client implements ClientInterface
{
    protected HTTP2Client $client;

    public function __construct(string $host, int $port = 80, bool $ssl = false, array $settings = [])
    {
        $this->client = new HTTP2Client($host, $port, $ssl);

        if ($settings) {
            $this->client->set($settings);
        }

        $this->client->connect();
    }

    public function set(array $settings): bool
    {
        return $this->client->set($settings);
    }

    public function send(RequestInterface $request): int
    {
        $res = $this->client->send($this->transformRequest($request));
        if ($res === false) {
            throw new HttpClientException($this->client->errMsg, $this->client->errCode);
        }

        return $res;
    }

    public function recv(float $timeout = 0): ResponseInterface
    {
        $response = $this->client->recv($timeout);
        if ($response === false) {
            throw new HttpClientException($this->client->errMsg, $this->client->errCode);
        }

        return $this->transformResponse($response);
    }

    public function write(int $streamId, mixed $data, bool $end = false): bool
    {
        return $this->client->write($streamId, $data, $end);
    }

    public function ping(): bool
    {
        return $this->client->ping();
    }

    public function close(): bool
    {
        return $this->client->close();
    }

    public function isConnected(): bool
    {
        return $this->client->connected;
    }

    private function transformResponse(SwResponse $request): ResponseInterface
    {
        return new Response(
            $request->streamId,
            $request->statusCode,
            $request->headers ?? [],
            $request->data
        );
    }

    private function transformRequest(RequestInterface $request): SwRequest
    {
        $req = new SwRequest();
        $req->method = $request->getMethod();
        $req->path = $request->getPath();
        $req->headers = $request->getHeaders();
        $req->data = $request->getBody();
        $req->pipeline = $request->isPipeline();
        $req->usePipelineRead = $request->isPipeline();
        return $req;
    }
}
