<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Exception\RuntimeException;
use Kernel\Log\Log;
use Kernel\Util\File;

/**
 * 此类已废弃
 */
class Vendor
{

    private array $loaders = [];

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/vendor";

    /**
     * @param string $name
     * @param string $env
     * @return void
     * @throws RuntimeException
     */
    public function add(string $name, string $env): void
    {
        $autoload = BASE_PATH . $env . "/{$name}/Vendor/autoload.php";

        if (!is_file($autoload)) {
            return;
        }

        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($autoload) {
            $items = Plugin::inst()->decrypt($contents);
            if (!in_array($autoload, $items)) {
                $items[] = $autoload;
            }
            return Plugin::inst()->encrypt($items);
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
        $autoload = BASE_PATH . $env . "/{$name}/Vendor/autoload.php";
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($autoload) {
            $items = Plugin::inst()->decrypt($contents);
            $index = array_search($autoload, $items);
            if ($index !== false) {
                unset($items[$index]);
                $items = array_values($items);
            }
            return Plugin::inst()->encrypt($items);
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
     * @throws \ReflectionException
     */
    public function load(): void
    {
        try {
            $list = $this->list();
            foreach ($list as $autoload) {
                if (is_file($autoload)) {
                    $md5 = md5_file($autoload);
                    if (isset($this->loaders[$md5])) {
                        continue;
                    }
                    include_once($autoload);
                    $this->loaders[$md5] = true;
                }
            }
        } catch (\Throwable $e) {
            Log::inst()->error("plugin vendor failed to load: " . $e->getMessage());
        }
    }
}