<?php
declare(strict_types=1);

namespace Kernel\Component;

use Kernel\Container\Di;

trait Inject
{
    /**
     * @param mixed ...$args
     * @throws \ReflectionException
     */
    public function __construct(mixed ...$args)
    {
        Di::inst()->inject($this);
        parent::__construct(...$args);
    }



}