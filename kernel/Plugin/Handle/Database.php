<?php
declare(strict_types=1);

namespace Kernel\Plugin\Handle;

interface Database
{
    /**
     * @return void
     */
    public function install(): void;

    /**
     * @return void
     */
    public function uninstall(): void;
}