<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Annotation\Collector;
use Kernel\Annotation\Thread;

use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Exception\RuntimeException;
use Kernel\Log\Const\Color;
use Kernel\Plugin\Entity\ProcessInfo;
use Kernel\Util\Config;
use Kernel\Util\File;
use Kernel\Log\Log;
use Swoole\Process\Manager;
use Swoole\Process\Pool;
use Symfony\Component\Finder\Finder;

class Process
{
    use Singleton;

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/process";

    /**
     * @param ProcessInfo $processInfo
     * @return bool
     */
    public function exist(ProcessInfo $processInfo): bool
    {
        $runtime = self::CACHE_FILE;
        if (!file_exists($runtime)) {
            return false;
        }

        $items = File::read($runtime, function (string $contents) {
            return Plugin::inst()->decrypt($contents);
        }) ?: [];

        foreach ($items as $p) {
            if ($p->namespace == $processInfo->namespace && $p->name == $processInfo->name && $p->plugin->name == $processInfo->plugin->name && $p->env == $processInfo->env) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function add(string $name, string $env): void
    {
        $path = BASE_PATH . $env . "/{$name}";
        if (!is_dir($path . "/Process")) {
            return;
        }
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($name, $env, $path) {
            $plugin = Plugin::inst()->getPlugin($name, $env);
            $processItems = Plugin::inst()->decrypt($contents);
            $processFiles = Finder::create()->in($path . "/Process")->depth("== 0")->ignoreDotFiles(true)->files()->name("*.php");
            $namespace = Usr::inst()->pathToNamespace($env);
            foreach ($processFiles as $processFile) {
                $className = "{$namespace}\\{$name}\\Process\\" . str_replace(".php", "", $processFile->getFilename());
                if (!class_exists($className)) {
                    continue;
                }
                Collector::instance()->classParse($className, function (\ReflectionAttribute $attribute) use ($env, $name, $className, $plugin, &$processItems) {
                    if ($attribute->getName() == Thread::class) {
                        $arguments = $attribute->getArguments();
                        $processInfo = new ProcessInfo($arguments['name'], $className, $plugin, (int)($arguments['num'] ?? 1), $env);
                        if (!$this->exist($processInfo)) {
                            $processItems[] = $processInfo;
                        }
                    }
                });
            }
            return Plugin::inst()->encrypt($processItems);
        });
    }

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function del(string $name, string $env): void
    {
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($env, $name) {
            $items = Plugin::inst()->decrypt($contents);
            /**
             * @var ProcessInfo $item
             */
            foreach ($items as $index => $item) {
                if ($item->plugin->name == $name && $item->env == $env) {
                    unset($items[$index]);
                }
            }
            $items = array_values($items);
            return Plugin::inst()->encrypt($items);
        });
    }


    /**
     * @return void
     */
    public function started(): void
    {
        if (!App::$cli) {
            return;
        }

        $config = Config::get("cli-server");
        $processItems = File::read(self::CACHE_FILE, function (string $contents) {
            return Plugin::inst()->decrypt($contents);
        }) ?: [];

        $processName = $config['name'] . "." . $config['port'];

        $process = new \Swoole\Process(function () use ($config, $processItems, $processName) {
            \Kernel\Util\Process::setName($processName . ".plugin.main");
            $pm = new Manager();
            if (count($processItems) > 0) {
                /**
                 * @var ProcessInfo $processInfo
                 */
                foreach ($processItems as $processInfo) {
                    for ($i = 0; $i < $processInfo->num; $i++) {
                        $pm->add(function (Pool $pool, int $workerId) use ($processName, $i, $processInfo, $config) {
                            \Kernel\Util\Process::setName($processName . ".plugin.{$processInfo->name}#{$processInfo->env}#{$i}");
                            Di::instance()->make($processInfo->namespace, $processInfo->plugin)->handle();
                        }, true);
                    }
                    Log::inst()->stdout("[PLUGIN]:<{$processInfo->plugin->info['name']}> [{$processInfo->env}] 共({$processInfo->num})個綫程啓動", Color::YELLOW, true);
                }
            }

            $pm->add(function () use ($processName, $config) {
                \Kernel\Util\Process::setName($processName . ".plugin.sync");
                Sync::inst()->started();
            }, true);

            $pm->start();
        });
        $process->start();
    }
}