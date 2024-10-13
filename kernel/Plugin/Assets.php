<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Component\Singleton;
use Kernel\Exception\RuntimeException;
use Kernel\Util\File;

class Assets
{
    use Singleton;


    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/assets";


    /**
     * @param string $path
     * @return void
     * @throws RuntimeException
     */
    public function add(string $path): void
    {
        if (!is_dir(BASE_PATH . $path) && !is_file(BASE_PATH . $path)) {
            return;
        }
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($path) {
            $assets = unserialize($contents) ?: [];
            if (!in_array($path, $assets)) {
                $assets[] = $path;
            }
            return serialize($assets);
        });
    }


    /**
     * @param string $path
     * @return void
     * @throws RuntimeException
     */
    public function del(string $path): void
    {
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($path) {
            $assets = unserialize($contents) ?: [];
            $filteredAssets = array_filter($assets, function ($item) use ($path) {
                return $item !== $path;
            });
            return serialize(array_values($filteredAssets));
        });
    }


    /**
     * @return array
     */
    public function list(): array
    {
        return File::read(self::CACHE_FILE, function (string $contents) {
            return unserialize($contents) ?: [];
        }) ?: [];
    }
}