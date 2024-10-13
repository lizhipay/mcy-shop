<?php
declare (strict_types=1);

namespace Kernel\Task;

use Kernel\Component\Singleton;
use Kernel\Context\App;
use Kernel\Server\CLI;
use Swoole\Server;

class Task
{
    use Singleton;

    /**
     * @var Server
     */
    private Server $server;


    public function __construct()
    {

        if (!App::$cli) {
            return;
        }

        $this->server = CLI::instance()->getHttpServer();
    }


    /**
     * @param Interface\Task $task
     * @param callable|null $finish
     * @return void
     */
    public function task(\Kernel\Task\Interface\Task $task, ?callable $finish = null): void
    {
        if (!App::$cli) {
            $handle = $task->handle();
            is_callable($finish) && call_user_func_array($finish, [$handle]);
            return;
        }

        $this->server->task(data: [
            "callable" => $task
        ], finishCallback: function (Server $server, int $taskId, mixed $data) use ($finish) {
            is_callable($finish) && call_user_func_array($finish, [$data]);
        });
    }


    /**
     * @param Interface\Task $task
     * @return mixed
     */
    public function taskGetResults(\Kernel\Task\Interface\Task $task): mixed
    {
        if (!App::$cli) {
            return $task->handle();
        }
        return $this->server->taskwait(data: [
            "callable" => $task
        ]);
    }
}