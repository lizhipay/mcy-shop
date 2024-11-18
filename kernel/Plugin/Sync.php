<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use App\Command\Service;
use Kernel\Cache\Cache;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Exception\RuntimeException;
use Kernel\Server\Swoole\Constant;
use Kernel\Util\File;
use Swoole\Coroutine;

class Sync
{
    use Singleton;

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/sync";


    /**
     * @param int $state
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function add(int $state, string $name, string $env): void
    {
        if (!App::$cli) {
            return;
        }

        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($state, $env, $name) {
            $waits = Plugin::inst()->decrypt($contents);

            $key = md5($name . $env);

            if (isset($waits[$key]) && $state == \Kernel\Plugin\Const\Plugin::STATE_STOP && $waits[$key][2] == \Kernel\Plugin\Const\Plugin::STATE_STOP) {
                unset($waits[$key]);
            } else {
                $waits[md5($name . $env)] = [$name, $env, $state];
            }

            return Plugin::inst()->encrypt($waits);
        });
    }

    /**
     * @return array
     */
    public function list(): array
    {
        return File::read(self::CACHE_FILE, function (string $contents) {
            return Plugin::inst()->decrypt($contents);
        }) ?: [];
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        File::remove(BASE_PATH . "/runtime/plugin/sync");
    }

    /**
     * @param string $name
     * @param string $env
     * @return bool
     * @throws \ReflectionException
     */
    public function has(string $name, string $env): bool
    {
        $waits = Sync::inst()->list();
        foreach ($waits as $wait) {
            if ($wait[0] == $name && $wait[1] == $env) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return void
     */
    public function started(): void
    {
        if (!App::$cli) {
            return;
        }

        $init = true;
        while (true) {
            $waits = Sync::inst()->list();
            $lastRequestTime = (int)Cache::inst()->get(Constant::CLI_LAST_REQUEST_TIME);
            if ($init || (count($waits) > 0 && (time() - $lastRequestTime) > 3)) {
                foreach ($waits as $wait) {
                    $wait[2] != 2 && Plugin::inst()->setState($wait[0], (int)$wait[2], $wait[1]);
                }
                Sync::inst()->clear();
                //重启
                !$init && Di::inst()->make(Service::class)->restart();
            }
            Coroutine::sleep(1);
            $init && ($init = false);
        }
    }
}