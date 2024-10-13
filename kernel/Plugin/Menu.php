<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Component\Singleton;
use Kernel\Exception\RuntimeException;
use Kernel\Util\File;

class Menu
{

    use Singleton;

    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/menu";

    /**
     * @param string $name
     * @param array $menu
     * @param string $usr
     * @return void
     * @throws RuntimeException
     */
    public function add(string $name, array $menu, string $usr = "*"): void
    {
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($menu, $name, $usr) {
            $menus = Plugin::inst()->decrypt($contents);
            $menus[$usr][$name] = $menu;
            return Plugin::inst()->encrypt($menus);
        });
    }

    /**
     * @param string $name
     * @param string $usr
     * @return void
     * @throws RuntimeException
     */
    public function del(string $name, string $usr = "*"): void
    {
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($name, $usr) {
            $menus = Plugin::inst()->decrypt($contents);
            unset($menus[$usr][$name]);
            return Plugin::inst()->encrypt($menus);
        });
    }

    /**
     * @param string $usr
     * @return array
     */
    public function list(string $usr = "*"): array
    {
        $menus = File::read(self::CACHE_FILE, function (string $contents) use ($usr) {
            return Plugin::inst()->decrypt($contents);
        });
        $a = $menus[$usr] ?? [];
        $b = [];
        foreach ($a as $items) {
            foreach ($items as $item) {
                $b[] = $item;
            }
        }
        return $b;
    }
}