<?php
declare(strict_types=1);

namespace Kernel\Plugin\Abstract;


use Kernel\Plugin\Entity\Plugin as PluginEntity;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

abstract class WebSocket implements \Kernel\Plugin\Handle\WebSocket
{
    /**
     * @var PluginEntity
     */
    protected PluginEntity $plugin;

    /**
     * @var Server
     */
    protected Server $server;


    /**
     * @param PluginEntity $plugin
     * @param Server $server
     */
    public function __construct(PluginEntity $plugin, Server $server)
    {
        $this->plugin = $plugin;
        $this->server = $server;
    }


    /**
     * 推送消息
     * @param int $fd
     * @param string $data
     * @return void
     * @throws \ReflectionException
     */
    protected function push(int $fd, string $data): void
    {
        \Kernel\Plugin\WebSocket::inst()->push($fd, $data);
    }

    /**
     * 强制杀死客户连接
     * @param int $fd
     * @return void
     */
    protected function kill(int $fd): void
    {
        $this->server->close($fd);
    }

    /**
     * @param int $fd
     * @param int $code
     * @param string $reason
     * @return bool
     */
    protected function disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
    {
        return $this->server->disconnect($fd, $code, $reason);
    }

    /**
     * @param int $fd
     * @return bool
     */
    public function isEstablished(int $fd): bool
    {
        return $this->server->isEstablished($fd);
    }


    /**
     * @param Frame|string $data
     * @param int $opcode
     * @param int $flags
     * @return string
     */
    public function pack(Frame|string $data, int $opcode = SWOOLE_WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
    {
        return $this->server::pack($data, $opcode, $flags);
    }


    /**
     * @param string $data
     * @return Frame
     */
    public function unpack(string $data): Frame
    {
        return $this->server::unpack($data);
    }
}