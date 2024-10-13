<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Annotation\Collector;
use Kernel\Component\Singleton;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Const\Plugin as PGN;
use Kernel\Plugin\Entity\HookInfo;
use Kernel\Util\File;
use Symfony\Component\Finder\Finder;

class Hook
{

    use Singleton;

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/hook";

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function del(string $name, string $env): void
    {
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($name, $env) {
            $hooks = Plugin::inst()->decrypt($contents);

            //卸载hook
            if (isset($hooks["GLOBAL"])) {
                foreach ($hooks["GLOBAL"] as $point => $hooker) {
                    /**
                     * @var HookInfo $item
                     */
                    foreach ($hooker as $index => $item) {
                        if ($item->name == $name && $env == $item->env) {
                            unset($hooks["GLOBAL"][$point][$index]);
                        }
                    }
                    //重新排序
                    $hooks["GLOBAL"][$point] = array_values($hooks["GLOBAL"][$point]);
                }
            }

            if (isset($hooks[$env])) {
                foreach ($hooks[$env] as $point => $hooker) {
                    /**
                     * @var HookInfo $item
                     */
                    foreach ($hooker as $index => $item) {
                        if ($item->name == $name && $env == $item->env) {
                            unset($hooks[$env][$point][$index]);
                        }
                    }
                    //重新排序
                    $hooks[$env][$point] = array_values($hooks[$env][$point]);
                }
            }


            return Plugin::inst()->encrypt($hooks);
        });
    }

    /**
     * @param string $name
     * @param string $env
     * @param callable|null $callable
     * @return array
     * @throws \ReflectionException
     */
    public function scan(string $name, string $env, callable $callable = null): array
    {
        $path = BASE_PATH . $env . "/{$name}";
        $plugin = Plugin::inst()->getPlugin($name, $env);
        $namespace = Usr::inst()->pathToNamespace($env);

        if (!is_dir($path . "/Hook")) {
            return [];
        }

        $files = Finder::create()->in($path . "/Hook")->depth("== 0")->ignoreDotFiles(true)->files()->name("*.php");
        $scans = [];

        foreach ($files as $file) {
            $className = $namespace . "\\{$name}\\Hook\\" . str_replace(".php", "", $file->getFilename());
            if (!class_exists($className)) {
                continue;
            }
            $methods = get_class_methods($className);
            foreach ($methods as $method) {
                Collector::instance()->methodParse($className, $method, function (\ReflectionAttribute $attribute) use ($callable, &$scans, $env, $plugin, $name, $method, $className) {
                    if ($attribute->getName() == \Kernel\Annotation\Hook::class) {
                        $arguments = $attribute->getArguments();
                        $point = $arguments['point'];
                        $hookInfo = new HookInfo($name, $className, $method, $plugin, $env, $point);
                        $scans[$point][] = $hookInfo;
                        is_callable($callable) && call_user_func_array($callable, [$point, $hookInfo]);
                    }
                });
            }
        }

        return $scans;
    }


    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function add(string $name, string $env): void
    {
        $path = BASE_PATH . $env . "/{$name}";
        $plugin = Plugin::inst()->getPlugin($name, $env);
        $namespace = Usr::inst()->pathToNamespace($env);

        if (!is_dir($path . "/Hook")) {
            return;
        }

        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($plugin, $env, $name, $namespace, $path) {
            $hooks = Plugin::inst()->decrypt($contents);
            $files = Finder::create()->in($path . "/Hook")->depth("== 0")->ignoreDotFiles(true)->files()->name("*.php");
            foreach ($files as $file) {
                $className = $namespace . "\\{$name}\\Hook\\" . str_replace(".php", "", $file->getFilename());
                if (!class_exists($className)) {
                    continue;
                }
                $methods = get_class_methods($className);
                foreach ($methods as $method) {
                    Collector::instance()->methodParse($className, $method, function (\ReflectionAttribute $attribute) use ($env, $plugin, $name, $method, $className, &$hooks) {
                        if ($attribute->getName() == \Kernel\Annotation\Hook::class) {
                            $arguments = $attribute->getArguments();
                            $point = $arguments['point'];
                            $weight = $arguments['weight'] ?? 100;
                            $hookInfo = new HookInfo($name, $className, $method, $plugin, $env, $point, $weight);
                            if (!$this->exist($point, $hookInfo)) {
                                if ($plugin->info[PGN::HOOK_SCOPE] == PGN::HOOK_SCOPE_GLOBAL) {
                                    $hooks["GLOBAL"][$point][] = $hookInfo;
                                } else {
                                    $hooks[$env][$point][] = $hookInfo;
                                }
                            }
                        }
                    });
                }
            }


            foreach ($hooks as &$points) {
                foreach ($points as &$hks) {
                    usort($hks, function ($a, $b) {
                        return ($b?->weight ?? 100) <=> ($a?->weight ?? 100);
                    });
                }
            }

            return Plugin::inst()->encrypt($hooks);
        });
    }


    /**
     * @param int $point
     * @param HookInfo $hookInfo
     * @return bool
     */
    public function exist(int $point, HookInfo $hookInfo): bool
    {
        $runtime = self::CACHE_FILE;
        if (!file_exists($runtime)) {
            return false;
        }

        $hooks = File::read($runtime, function (string $contents) {
            return Plugin::inst()->decrypt($contents);
        }) ?: [];

        if (isset($hooks[$hookInfo->env][$point])) {

            /**
             * @var HookInfo $hook
             */
            foreach ($hooks[$hookInfo->env][$point] as $hook) {
                if ($hook->namespace == $hookInfo->namespace && $hook->method == $hookInfo->method && $hook->name == $hookInfo->name && $hook->env == $hookInfo->env) {
                    return true;
                }
            }
        }
        if (isset($hooks["GLOBAL"][$point])) {
            foreach ($hooks["GLOBAL"][$point] as $hook) {
                if ($hook->namespace == $hookInfo->namespace && $hook->method == $hookInfo->method && $hook->name == $hookInfo->name && $hook->env == $hookInfo->env) {
                    return true;
                }
            }
        }
        return false;
    }
}