<?php

namespace App\Command;

use Kernel\Console\Command;
use Kernel\Exception\JSONException;
use Kernel\Plugin\Usr;

class Plugin extends Command
{

    /**
     * @param string $name
     * @param int $userId
     * @return void
     * @throws JSONException
     */
    public function stop(string $name, int $userId): void
    {
        \Kernel\Plugin\Plugin::instance()->stop($name, Usr::inst()->userToEnv($userId));
    }

    /**
     * @param int $userId
     * @return void
     */
    public function list(int $userId): void
    {
        $plugins = \Kernel\Plugin\Plugin::instance()->getStartups(\Kernel\Plugin\Const\Plugin::TYPE_ANY, Usr::inst()->userToEnv($userId));
        echo "\033[1;32m正在运行的插件（插件标识-插件名称）：\033[0m\n";
        foreach ($plugins as $item) {
            echo "\033[1;32m[{$item->name}]\033[0m - \033[1;33m「{$item->info['name']}」\033[0m\n";
        }
    }
}