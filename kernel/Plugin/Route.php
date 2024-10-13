<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Component\Singleton;
use Kernel\Context\Interface\Request;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Aes;
use Kernel\Util\Context;
use Kernel\Util\File;
use Kernel\Util\Str;

class Route
{
    use Singleton;


    public const CACHE_FILE = BASE_PATH . "/runtime/plugin/route";


    /**
     * @param array $router
     * @param string $name
     * @param string $prefix
     * @param string $usr
     * @return void
     * @throws RuntimeException
     */
    public function add(array $router, string $name, string $prefix = "plugin", string $usr = "*"): void
    {
        foreach ($router as $item) {
            if ($usr == "*") {
                $route = sprintf("/%s/%s/%s", $prefix, Str::camelToSnake($name), trim($item['route'], "/"));
            } else {
                $route = sprintf("/%s/%d/%s/%s", $prefix, $usr, Str::camelToSnake($name), trim($item['route'], "/"));
            }

            $method = $item['method'];
            $class = [$item['class'], $item['action']];
            File::writeForLock(self::CACHE_FILE, function (string $contents) use ($usr, $route, $method, $class) {
                $pass = Plugin::inst()->getHwId();
                $routes = unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
                $obj = new \Kernel\Context\Route();
                $obj->setAction($class[1]);
                $obj->setClass($class[0]);
                $obj->setMethod(strtoupper($method));
                $obj->setRoute("/" . trim($route, "/"));
                $routes[$usr][$method][$route] = $obj;

                return Aes::encrypt(serialize($routes), $pass, $pass, false);
            });
        }
    }


    /**
     * @param string $route
     * @param string $method
     * @param string $usr
     * @return void
     * @throws RuntimeException
     */
    private function remove(string $route, string $method, string $usr = "*"): void
    {
        File::writeForLock(self::CACHE_FILE, function (string $contents) use ($usr, $route, $method) {
            $pass = Plugin::inst()->getHwId();
            $routes = unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
            unset($routes[$usr][$method][$route]);
            return Aes::encrypt(serialize($routes), $pass, $pass, false);
        });
    }

    /**
     * @param string|null $usr
     * @return array
     */
    public function list(?string $usr = null): array
    {
        if ($usr == null) {
            /**
             * @var Request $var
             */
            $var = Context::get(Request::class);
            $rt = explode("/", trim($var->uri(), "/"));
            if (strtolower($rt[0]) != "plugin" || !isset($rt[1])) {
                return [];
            }

            if (!preg_match("/^\d+$/", $rt[1])) {
                //主站
                $usr = "*";
            } else {
                //分站
                if (!isset($rt[2])) {
                    return [];
                }
                $usr = $rt[1];
            }
        }

        $list = File::read(self::CACHE_FILE, function (string $contents) {
            $pass = Plugin::inst()->getHwId();
            return unserialize(Aes::decrypt($contents, $pass, $pass, false)) ?: [];
        });

        return $list[$usr] ?? [];
    }

    /**
     * @param string $name
     * @param string $prefix
     * @param string $usr
     * @return void
     * @throws RuntimeException
     */
    public function del(string $name, string $prefix = "plugin", string $usr = "*"): void
    {
        $routes = $this->list($usr);
        $keywords = $usr == "*" ? sprintf("%s/%s", $prefix, Str::camelToSnake($name)) : sprintf("%s/%d/%s", $prefix, $usr, Str::camelToSnake($name));
        foreach ($routes as $methods) {
            foreach ($methods as $method) {
                if (str_contains($method->route(), $keywords)) {
                    $this->remove($method->route(), $method->method(), $usr);
                }
            }
        }
    }
}