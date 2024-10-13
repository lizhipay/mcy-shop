<?php
declare(strict_types=1);

namespace Kernel\Database;

use Generator;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use Kernel\Component\Singleton;

/**
 * DB Helper.
 * @method static Builder table(Expression|string $table)
 * @method static Expression raw($value)
 * @method static selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static Generator cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static array prepareBindings(array $bindings)
 * method static transaction(\Closure $callback, int $attempts = 1)
 * @method static beginTransaction()
 * @method static rollBack()
 * @method static commit()
 * @method static int transactionLevel()
 * @method static array pretend(\Closure $callback)
 * @method static ConnectionInterface connection(string $pool)
 */
class Db
{
    use Singleton;

    public function __call($name, $arguments)
    {
        if ($name === 'connection') {
            return $this->__connection(...$arguments);
        }
        return $this->__connection()->{$name}(...$arguments);
    }


    public static function __callStatic($name, $arguments)
    {
        $db = Db::instance();
        if ($name === 'connection') {
            return $db->__connection(...$arguments);
        }
        return $db->__connection()->{$name}(...$arguments);
    }

    private function __connection(): ConnectionInterface
    {
        return Connection::instance()->get();
    }


    /**
     * 执行带有隔离级别设置的事务，并支持重试机制。
     *
     * @param callable $callback 要在事务中执行的回调函数。
     * @param string $level 事务的隔离级别。
     * @param int $attempts 重试次数，默认为1，表示不重试。
     * @return mixed 回调函数的执行结果。
     * @throws \Throwable
     */
    public static function transaction(callable $callback, string $level = \Kernel\Database\Const\Db::ISOLATION_REPEATABLE_READ, int $attempts = 1): mixed
    {
        $attempt = 0;
        while ($attempt < $attempts) {
            try {
                self::statement("SET SESSION TRANSACTION ISOLATION LEVEL {$level}");
                self::beginTransaction();
                $result = $callback();
                self::commit();
                return $result; // 如果成功，返回结果
            } catch (\Throwable $e) {
                self::rollBack(); // 如果失败，回滚事务
                if (++$attempt < $attempts) {
                    continue; // 如果还有重试次数，继续下一次尝试
                } else {
                    throw $e; // 如果重试次数用尽，抛出异常
                }
            }
        }

        throw new \Exception("mysql transaction encountered an unknown error", 10881);
    }

}
