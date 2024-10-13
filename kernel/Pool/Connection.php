<?php
declare (strict_types=1);

namespace Kernel\Pool;


interface Connection
{
    /**
     * 创建资源
     * @return mixed
     */
    public function createObject(): mixed;
}