<?php
declare(strict_types=1);

namespace Kernel\Plugin\Const;

interface WebSocket
{
    public const LOCALHOST = "127.0.0.1";
    public const FD_KEY = "websocket:%d";
    public const MESSAGE_TYPE = "type";
    public const MESSAGE_TYPE_CLIENT = "client";
    public const MESSAGE_TYPE_PUSH = "push";
    public const MESSAGE_NAME = "name";
    public const MESSAGE_ENV = "env";

    public const MESSAGE_FD = "fd";

    public const MESSAGE_WORKER_ID = "worker_id";
}