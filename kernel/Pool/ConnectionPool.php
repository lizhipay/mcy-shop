<?php
declare (strict_types=1);

namespace Kernel\Pool;


/**
 * 连接池
 */
class ConnectionPool extends \Swoole\ConnectionPool
{

    public function __construct(string $class, int $size)
    {
        \Swoole\ConnectionPool::__construct(function () use ($class) {
            return (new $class)->createObject();
        }, $size);
        $this->fill();
    }

}