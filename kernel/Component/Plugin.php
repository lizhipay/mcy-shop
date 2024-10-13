<?php
declare (strict_types=1);

namespace Kernel\Component;

use Kernel\Context\Interface\Route;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Entity\Plugin as PGI;
use Kernel\Plugin\Usr;
use Kernel\Util\Context;
use Kernel\Util\Str;

trait Plugin
{
    /**
     * @return PGI
     * @throws JSONException
     * @throws NotFoundException
     */
    public function getPlugin(): PGI
    {
        $var = Context::get(Route::class);
        $route = $var->route();
        $rt = explode("/", trim($route, "/"));
        if (strtolower($rt[0]) != "plugin") {
            throw new JSONException("当前[Trait]仅限插件控制器中调用");
        }

        if (!isset($rt[1])) {
            throw new JSONException("没有检测到插件");
        }

        if (!preg_match("/^\d+$/", $rt[1])) {
            //主站
            $name = Str::snakeToPascal($rt[1]);
            $env = Usr::MAIN;
        } else {
            //分站
            if (!isset($rt[2])) {
                throw new JSONException("没有检测到插件");
            }
            $name = Str::snakeToPascal($rt[2]);
            $env = Usr::inst()->userToEnv((int)$rt[1]);
        }

        $plugin = \Kernel\Plugin\Plugin::instance()->getPlugin($name, $env);
        if (!$plugin) {
            throw new JSONException("插件[{$name}]不存在");
        }
        return $plugin;
    }
}