<?php
declare (strict_types=1);

namespace Kernel\Database;

use Hyperf\Database\ConnectionInterface;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Pool\ConnectionPool;
use Swoole\Coroutine;

class Connection
{
    use Singleton;

    /**
     * @var array
     */
    private array $connections = [];


    /**
     * @return ConnectionInterface
     */
    public function get(): ConnectionInterface
    {
        if (!App::$cli) {
            if (isset($this->connections[0])) {
                return $this->connections[0];
            }
            return $this->connections[0] = (new \Kernel\Database\MysqlConnection())->createObject();
        }

        $cid = Coroutine::getCid();

        if (isset($this->connections[$cid])) {
            return $this->connections[$cid];
        }

        $di = Di::instance()->get(ConnectionPool::class);
        $this->connections[$cid] = $di->get();


        //返还连接
        \Swoole\Coroutine\defer(function () use ($cid, $di) {
            $di->put($this->connections[$cid]);
            unset($this->connections[$cid]);
        });
        return $this->connections[$cid];
    }

    /**
     * 释放链接
     * @return void
     */
    public function release(): void
    {
        if (!App::$cli) {
            return;
        }

        $cid = Coroutine::getCid();
        $this->connections[$cid] = null;
    }

    /**
     * 设置链接
     * @param ConnectionProxy $connection
     * @return void
     */
    public function set(ConnectionProxy $connection): void
    {
        if (!App::$cli) {
            return;
        }

        $cid = Coroutine::getCid();
        $this->connections[$cid] = $connection;
    }


    /**
     * @return int
     */
    public function usage(): int
    {
        return count($this->connections);
    }
}