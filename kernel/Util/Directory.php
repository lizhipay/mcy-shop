<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Exception\RuntimeException;

class Directory
{

    /**
     * @param string $path
     * @return void
     * @throws RuntimeException
     */
    public static function delete(string $path): void
    {
        // 检查目录是否存在
        if (!is_dir($path)) {
            return;
        }

        //递归删除目录
        if ($handle = opendir($path)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("{$path}/{$item}")) {
                        self::delete("{$path}/{$item}");
                    } else {
                        unlink("{$path}/{$item}");
                    }
                }
            }
            closedir($handle);
            if (!rmdir($path)) {
                throw new RuntimeException("无法删除目录: $path");
            }
        }
    }
}