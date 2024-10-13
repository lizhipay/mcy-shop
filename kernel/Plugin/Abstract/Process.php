<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

abstract class Process extends Plugin
{
    /**
     * @return void
     */
    public abstract function handle(): void;
}