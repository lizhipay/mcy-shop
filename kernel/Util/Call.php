<?php
declare(strict_types=1);

namespace Kernel\Util;

use Kernel\Context\App;
use Swoole\Coroutine;

class Call
{

    /**
     * @param callable $callable
     * @param mixed ...$args
     * @return void
     */
    public static function create(callable $callable, ...$args): void
    {
        if (!App::$cli) {
            call_user_func_array($callable, $args);
            return;
        }
        Coroutine::create($callable, ...$args);
    }


    /**
     * @param callable $callable
     * @return void
     */
    public static function defer(callable $callable): void
    {
        if (!App::$cli) {
            call_user_func($callable);
            return;
        }
        Coroutine::defer($callable);
    }
}