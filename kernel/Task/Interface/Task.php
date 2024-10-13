<?php
declare (strict_types=1);

namespace Kernel\Task\Interface;

interface Task
{
    /**
     * 执行任务子程序
     * @return mixed
     */
    public function handle(): mixed;
}