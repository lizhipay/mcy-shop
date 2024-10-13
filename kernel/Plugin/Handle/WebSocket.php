<?php
declare(strict_types=1);

namespace Kernel\Plugin\Handle;

use Kernel\Context\Interface\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface WebSocket
{

    /**
     * 消息到达
     * @param Frame $frame
     * @param Server $server
     * @return void
     */
    public function message(Frame $frame, Server $server): void;


    /**
     * 打开连接
     * @param Request $request
     * @param int $fd
     * @param Server $server
     * @return void
     */
    public function open(Request $request, int $fd, Server $server): void;


    /**
     * 关闭连接
     * @param int $fd
     * @param Server $server
     * @return void
     */
    public function close(int $fd, Server $server): void;
}