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

use Hyperf\Engine\Contract\Http\ServerInterface;
use Hyperf\Engine\Coroutine;
use Hyperf\HttpMessage\Server\Request;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Http\Server as HttpServer;
use Throwable;

class Server implements ServerInterface
{
    public string $host;

    public int $port;

    /**
     * @var callable
     */
    protected $handler;

    protected HttpServer $server;

    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function bind(string $name, int $port = 0): static
    {
        $this->host = $name;
        $this->port = $port;

        $this->server = new HttpServer($name, $port, reuse_port: true);
        return $this;
    }

    public function handle(callable $callable): static
    {
        $this->handler = $callable;
        return $this;
    }

    public function start(): void
    {
        $this->server->handle('/', function ($request, $response) {
            Coroutine::create(function () use ($request, $response) {
                try {
                    $handler = $this->handler;

                    $handler(Request::loadFromSwooleRequest($request), $response);
                } catch (Throwable $exception) {
                    $this->logger->critical((string) $exception);
                }
            });
        });

        $this->server->start();
    }

    public function close(): bool
    {
        $this->server->shutdown();

        return true;
    }
}
