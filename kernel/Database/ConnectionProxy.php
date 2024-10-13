<?php
declare(strict_types=1);

namespace Kernel\Database;


use Kernel\Context\App;

class ConnectionProxy extends \Hyperf\Database\MySqlConnection
{

    /**
     * @return ConnectionProxy
     * @throws \ReflectionException
     */
    public function reconnect(): ConnectionProxy
    {
        $this->pdo = self::createPdo();
        $connect = self::create($this->pdo);
        Connection::instance()->set($connect);
        return $connect;
    }

    /**
     * @param \PDO|null $pdo
     * @return ConnectionProxy
     * @throws \ReflectionException
     */
    public static function create(?\PDO $pdo = null): ConnectionProxy
    {
        $connection = new self(function () {
            return $pdo ?? self::createPdo();
        }, App::$database['database'], App::$database['prefix']);

        $connection->setEventDispatcher(Listener::instance()->query());
        return $connection;
    }

    /**
     * @return \PDO
     */
    public static function createPdo(): \PDO
    {
        return new \PDO(
            App::$database['driver'] . ":host=" . App::$database['host'] . ";dbname=" . App::$database['database'] . ";charset=" . App::$database['charset'] . ";collation=" . App::$database['collation'],
            App::$database['username'],
            App::$database['password']
        );
    }
}