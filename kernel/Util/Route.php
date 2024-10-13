<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Exception\NotFoundException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Usr;

class Route
{
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const PATCH = "PATCH";
    const DELETE = "DELETE";
    const HEAD = "HEAD";

    const TYPE_MENU = 0;
    const TYPE_PAGE = 1;
    const TYPE_ROUTE = 2;
    const TYPE_LEADER = 3;

    /**
     * @var \Kernel\Context\Interface\Route[]
     */
    private static array $route = [];

    /**
     * @param string $route
     * @param array $class
     * @param string $method
     * @return void
     * @throws RuntimeException
     */
    public static function add(string $route, array $class, string $method = "ALL"): void
    {
        File::writeForLock(BASE_PATH . "/runtime/route", function (string $contents) use ($route, $method, $class) {
            $routes = unserialize($contents) ?: [];
            $obj = new \Kernel\Context\Route();
            $obj->setAction($class[1]);
            $obj->setClass($class[0]);
            $obj->setMethod(strtoupper($method));
            $obj->setRoute("/" . trim($route, "/"));
            $routes[$method][$route] = $obj;
            return serialize($routes);
        });
    }

    /**
     * @param string $route
     * @param string $method
     * @return void
     * @throws RuntimeException
     */
    public static function del(string $route, string $method): void
    {
        File::writeForLock(BASE_PATH . "/runtime/route", function (string $contents) use ($route, $method) {
            $routes = unserialize($contents) ?: [];
            unset($routes[$method][$route]);
            return serialize($routes);
        });
    }


    /**
     * @return void
     */
    private static function loadData(): void
    {
        self::$route = File::read(BASE_PATH . "/runtime/route", function (string $contents) {
            return unserialize($contents) ?? [];
        });
    }


    /**
     * 路由合并
     * @param array $routes
     * @param array ...$arr
     * @return array
     */
    private static function mergeRoutes(array $routes, array ...$arr): array
    {
        foreach ($arr as $any) {
            foreach ($any as $method => $items) {
                foreach ($items as $route => $obj) {
                    $routes[$method][$route] = $obj;
                }
            }
        }
        return $routes;
    }

    /**
     * @param string $uri
     * @return \Kernel\Context\Interface\Route[]
     * @throws \ReflectionException
     */
    public static function list(string $uri = ""): array
    {
        self::loadData();
        return array_merge_recursive(self::$route, \Kernel\Plugin\Route::inst()->list());
    }

    /**
     * @param string $route
     * @param string $method
     * @return bool
     * @throws \ReflectionException
     */
    public static function has(string $route, string $method): bool
    {
        $routes = self::list($route);
        $route = "/" . trim($route, "/");
        return isset($routes[$method][$route]) || isset($routes["ALL"][$route]);
    }

    /**
     * @param string $route
     * @param string $method
     * @return \Kernel\Context\Interface\Route|null
     * @throws \ReflectionException
     */
    public static function get(string $route, string $method): ?\Kernel\Context\Interface\Route
    {
        $routes = self::list($route);
        $route = "/" . trim($route, "/");
        if (!self::has($route, $method)) {
            return null;
        }
        return $routes[$method][$route] ?? $routes["ALL"][$route];
    }
}