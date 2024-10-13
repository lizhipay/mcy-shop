<?php
declare(strict_types=1);

namespace Kernel\Database;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Model\Builder;

/**
 * @method static bool hasTable(string $table)
 * @method static array getColumnListing(string $table)
 * @method static array getColumnTypeListing(string $table)
 * @method static void dropAllTables()
 * @method static void dropAllViews()
 * @method static array getAllTables()
 * @method static array getAllViews()
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static string getColumnType(string $table, string $column)
 * @method static void table(string $table, \Closure $callback)
 * @method static void create(string $table, \Closure $callback))
 * @method static void drop(string $table)
 * @method static void dropIfExists(string $table)
 * @method static void rename(string $from, string $to)
 * @method static bool enableForeignKeyConstraints()
 * @method static bool disableForeignKeyConstraints()
 * @method static \Hyperf\Database\Connection getConnection()
 * @method static Builder setConnection(\Hyperf\Database\Connection $connection)
 * @method static void blueprintResolver(\Closure $resolver)
 */
class Schema
{
    public static function __callStatic($name, $arguments)
    {
        $connection = Connection::instance()->get();
        return $connection->getSchemaBuilder()->{$name}(...$arguments);
    }

    public function __call($name, $arguments)
    {
        return self::__callStatic($name, $arguments);
    }

    public function connection(): ConnectionInterface
    {
        return Connection::instance()->get();
    }
}
