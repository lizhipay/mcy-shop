<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Component\Singleton;

class Shell
{
    use Singleton;


    /**
     * @param string $command
     * @return string|false|null
     */
    public function exec(string $command): string|null|false
    {
        $command = str_replace("\r\n", "\n", $command);
        $command = str_replace("\r", "\n", $command);
        return shell_exec($command . " 2>&1");
    }
}