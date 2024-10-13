<?php
declare (strict_types=1);

use Kernel\Server\Swoole\Constant;
use Kernel\Plugin\Assets;
use Kernel\Util\Config;
use Kernel\Context\App;
use Kernel\Util\System;

$server = Config::get("server");
$workerNum = swoole_cpu_num();
$name = "mcy-shop";
$host = "0.0.0.0";
$port = System::getRandPort();

if (App::$install) {
    if (isset($server['cpu']) && $server['cpu'] != 'auto' && $server['cpu'] >= 1 && $server['cpu'] <= (swoole_cpu_num() * 2)) {
        $workerNum = $server['cpu'] * 2;
    }
    if (isset($server['host'])) {
        $host = $server['host'];
    }
    if (isset($server['port'])) {
        $port = $server['port'];
    }
    if (isset($server['name'])) {
        $name = $server['name'];
    }
} else {
    $workerNum = 1;
}

return [
    'name' => $name,
    'host' => $host,
    'port' => $port,
    'options' => [
        Constant::OPTION_HTTP_COMPRESSION => true,
        Constant::OPTION_HTTP_COMPRESSION_LEVEL => 9,
        Constant::OPTION_COMPRESSION_MIN_LENGTH => 1,
        Constant::OPTION_REACTOR_NUM => $workerNum,
        Constant::OPTION_WORKER_NUM => $workerNum,
        Constant::OPTION_TASK_WORKER_NUM => 1,
        Constant::OPTION_DOCUMENT_ROOT => BASE_PATH,
        Constant::OPTION_STATIC_HANDLER_LOCATIONS => array_merge(['/assets', '/favicon.ico'], Assets::inst()->list()),
        Constant::OPTION_ENABLE_STATIC_HANDLER => true,
        Constant::OPTION_TASK_ENABLE_COROUTINE => true,
        Constant::OPTION_ENABLE_COROUTINE => true,
        Constant::OPTION_PID_FILE => BASE_PATH . '/runtime/pid',
        Constant::OPTION_OPEN_HTTP2_PROTOCOL => true,
        Constant::OPTION_SOCKET_BUFFER_SIZE => 4 * 1048576,
        Constant::OPTION_BUFFER_OUTPUT_SIZE => 4 * 1048576,
        Constant::OPTION_PACKAGE_MAX_LENGTH => 8 * 1048576,
        Constant::OPTION_MAX_REQUEST => 100000,
        Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_NONE,
        Constant::OPTION_HEARTBEAT_CHECK_INTERVAL => 60,
        Constant::OPTION_HEARTBEAT_IDLE_TIME => 600,
        Constant::OPTION_UPLOAD_MAX_FILESIZE => 1024 * 1024 * 1000,
        Constant::OPTION_UPLOAD_TMP_DIR => BASE_PATH . "/runtime/upload",
        Constant::OPTION_OPEN_WEBSOCKET_PING_FRAME => true
    ]
];