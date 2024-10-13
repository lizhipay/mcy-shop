<?php
declare (strict_types=1);

namespace Kernel\Util;

use Kernel\Context\App;
use Swoole\Coroutine;

class Context
{
    /**
     * @var array
     */
    private static array $container = [];

    /**
     * @param string $id
     * @param mixed $object
     * @return void
     */
    public static function set(string $id, mixed $object): void
    {
        if (!App::$cli) {
            self::$container[$id] = $object;
            return;
        }

        $cid = Coroutine::getCid();
        if ($cid > 0) {
            self::$container[$id][$cid] = $object;
            Call::defer(function () use ($id, $cid) {
                unset(self::$container[$id][$cid]);
            });
        } else {
            self::$container[$id] = $object;
        }
    }

    /**
     * @param string $id
     * @return mixed
     */
    public static function get(string $id): mixed
    {
        if (!self::has($id)) {
            return null;
        }

        if (!App::$cli) {
            return self::$container[$id];
        }

        $cid = Coroutine::getCid();
        if ($cid > 0) {
            return self::$container[$id][$cid];
        } else {
            return self::$container[$id];
        }
    }


    /**
     * @param string $id
     * @return void
     */
    public static function del(string $id): void
    {
        if (!App::$cli) {
            unset(self::$container[$id]);
            return;
        }

        $cid = Coroutine::getCid();
        if ($cid > 0) {
            unset(self::$container[$id][$cid]);
        } else {
            unset(self::$container[$id]);
        }
    }


    /**
     * @param string $id
     * @return bool
     */
    public static function has(string $id): bool
    {
        if (!App::$cli) {
            return isset(self::$container[$id]);
        }
        $cid = Coroutine::getCid();

        if ($cid > 0) {
            return isset(self::$container[$id][$cid]);
        } else {
            return isset(self::$container[$id]);
        }
    }
}