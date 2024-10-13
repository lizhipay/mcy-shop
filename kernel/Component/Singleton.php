<?php
declare (strict_types=1);

namespace Kernel\Component;


use Kernel\Container\Di;

trait Singleton
{
    /**
     * @var mixed
     */
    private static mixed $instance;

    /**
     * @param mixed ...$args
     * @return static
     * @throws \ReflectionException
     */
    public static function instance(...$args): static
    {
        if (!isset(static::$instance)) {
            static::$instance = new static(...$args);
            Di::inst()->inject(static::$instance);
        }
        return static::$instance;
    }


    /**
     * @param ...$args
     * @return static
     * @throws \ReflectionException
     */
    public static function inst(...$args): static
    {
        return self::instance(...$args);
    }
}