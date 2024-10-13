<?php
declare (strict_types=1);

namespace Kernel\Update;

interface Database
{
    /**
     * @return void
     */
    public function handle(): void;
}