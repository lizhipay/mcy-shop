<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Cache\Cache;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\Interface\Request;
use Kernel\Log\Log;
use Kernel\Plugin\Const\WebSocket as WebSocketConst;
use Kernel\Plugin\Handle\WebSocket as WS;
use Kernel\Server\CLI;
use Swoole\Timer;
use Swoole\WebSocket\Frame;
use Swoole\Websocket\Server;

class WebSocket
{
    use Singleton;

    /**
     * @var Server|null
     */
    protected ?Server $server = null;

    /**
     * @param Server $server
     * @param string $name
     * @param string $env
     * @return WebSocket|null
     * @throws \ReflectionException
     */
    private function handle(Server $server, string $name, string $env): ?WS
    {
        $plugin = Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            return null;
        }
        if ($plugin->state['run'] != 1) {
            return null;
        }

        if (!isset($plugin->handle) || !isset($plugin->handle[WS::class])) {
            return null;
        }

        $handle = $plugin->handle[WS::class];

        if (!class_exists($handle)) {
            return null;
        }

        $obj = new $handle($plugin, $server);
        Di::inst()->inject($obj);
        return $obj;
    }


    /**
     * @param string $name
     * @param string $env
     * @param Server $server
     * @param Request $request
     * @param int $fd
     * @return void
     * @throws \ReflectionException
     */
    public function open(string $name, string $env, Server $server, Request $request, int $fd): void
    {
        $handle = $this->handle($server, $name, $env);
        if (!$handle) {
            $server->close($fd);
            return;
        }

        Cache::instance()->set(sprintf(WebSocketConst::FD_KEY, $fd), [
            WebSocketConst::MESSAGE_TYPE => WebSocketConst::MESSAGE_TYPE_CLIENT,
            WebSocketConst::MESSAGE_NAME => $name,
            WebSocketConst::MESSAGE_ENV => $env,
            WebSocketConst::MESSAGE_FD => $fd,
            WebSocketConst::MESSAGE_WORKER_ID => CLI::instance()->getWorkerId()
        ]);

        call_user_func_array([$handle, "open"], [$request, $fd, $server]);
    }

    /**
     * @param Server $server
     * @param int $fd
     * @return void
     * @throws \ReflectionException
     */
    public function close(Server $server, int $fd): void
    {
        $key = sprintf(WebSocketConst::FD_KEY, $fd);
        $cache = Cache::instance()->get($key);
        if (!$cache) {
            return;
        }
        Cache::instance()->del($key);
        if ($cache[WebSocketConst::MESSAGE_TYPE] == WebSocketConst::MESSAGE_TYPE_PUSH) {
            return;
        }

        $handle = $this->handle($server, $cache[WebSocketConst::MESSAGE_NAME], $cache[WebSocketConst::MESSAGE_ENV]);
        if (!$handle) {
            return;
        }
        call_user_func_array([$handle, "close"], [$fd, $server]);
    }


    /**
     * @param Server $server
     * @param Frame $frame
     * @return void
     * @throws \ReflectionException
     */
    public function message(Server $server, Frame $frame): void
    {
        $key = sprintf(WebSocketConst::FD_KEY, $frame->fd);
        $cache = Cache::instance()->get($key);

        if (!$cache) {
            $server->close($frame->fd);
            return;
        }

        $handle = $this->handle($server, $cache[WebSocketConst::MESSAGE_NAME], $cache[WebSocketConst::MESSAGE_ENV]);
        if (!$handle) {
            $server->close($frame->fd);
            return;
        }

        if ($frame->data == "PING") {
            $server->push($frame->fd, "PONG");
            return;
        }

        call_user_func_array([$handle, "message"], [$frame, $server]);
    }


    /**
     * @param int $workerId
     * @return void
     */
    public function deathCheck(int $workerId): void
    {
        Timer::tick(60000, function () use ($workerId) {
            try {
                $client = Cache::instance()->search("websocket:");
                foreach ($client as $key => $value) {
                    $fd = (int)$value[WebSocketConst::MESSAGE_FD];
                    $wid = (int)$value[WebSocketConst::MESSAGE_WORKER_ID];
                    if ($wid == $workerId && !$this->server->exists($fd)) {
                        $this->close($this->server, $fd);
                    }
                }
            } catch (\Throwable $e) {
                Log::inst()->error("[WebSocket]->" . $e->getMessage());
            }
        });
    }

    /**
     * @param Server $server
     * @return void
     */
    public function setServer(Server $server): void
    {
        $this->server = $server;
    }

    /**
     * @throws \ReflectionException
     */
    public function push(int $fd, string $data): void
    {
        $key = sprintf(WebSocketConst::FD_KEY, $fd);
        $cache = Cache::instance()->get($key);
        if (!$cache) {
            return;
        }

        if (isset($cache[WebSocketConst::MESSAGE_WORKER_ID])) {
            $wid = $cache[WebSocketConst::MESSAGE_WORKER_ID];
            if ($wid == CLI::inst()->getWorkerId()) {
                if (!$this->server->exist($fd)) {
                    return;
                }
                $this->server->push($fd, $data);
            } else {
                $this->server->sendMessage([
                    "type" => "ws_push",
                    "data" => [
                        "fd" => $fd,
                        "data" => $data
                    ]
                ], $wid);
            }
        }
    }

    /**
     * @param Server $server
     * @param array $message
     * @return void
     */
    public function pipeMessage(Server $server, array $message): void
    {
        if (!isset($message['fd'], $message['data'])) {
            return;
        }
        if (!$this->server->exist($message['fd'])) {
            return;
        }
        $this->server->push($message['fd'], $message['data']);
    }
}