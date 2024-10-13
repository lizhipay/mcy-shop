<?php
declare (strict_types=1);

namespace Kernel\Service;

use App\Command\Service;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Util\Config;
use Kernel\Util\Shell;
use Swoole\Process;

class App
{
    use Singleton;

    private Process $restartWaitProcess;

    /**
     * @return void
     */
    public function startRestartWaitProcess(): void
    {
        // 创建一个子进程
        $this->restartWaitProcess = new Process(function (Process $worker) {
            $config = Config::get("cli-server");
            $worker->name($config['name'] . "." . $config['port'] . ".restart");
            $worker->setBlocking(true);
            while (true) {
                $msg = $worker->read();
                if ($msg === "restart") {
                    Di::inst()->make(Service::class)->restart();
                    break;
                } else {
                    usleep(300000);
                }
            }
        });
        $this->restartWaitProcess->start();
    }

    /**
     * 重启service服务
     * @return void
     */
    public function restart(): void
    {
        if (!\Kernel\Context\App::$cli) {
            return;
        }

        $this->restartWaitProcess->write("restart");
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function shutdown(): void
    {
        if (!\Kernel\Context\App::$cli) {
            return;
        }
        $config = Config::get("cli-server");
        Shell::inst()->exec("pkill -f {$config['name']}");
    }
}