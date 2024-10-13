<?php
declare (strict_types=1);

namespace Kernel\Database;

use Kernel\Component\Singleton;
use Rah\Danpu\Import;

class Dump
{
    use Singleton;

    /**
     * @param string $sql
     * @param string $host
     * @param string $db
     * @param string $username
     * @param string $password
     * @return void
     */
    public function import(string $sql, string $host, string $db, string $username, string $password): void
    {
        $hosts = explode(":", $host);
        $tmp = BASE_PATH . '/runtime/dump';
        if (!is_dir($tmp)) {
            mkdir($tmp, 0777, true);
        }
        $dump = new \Rah\Danpu\Dump();
        $dump
            ->file($sql)
            ->dsn('mysql:dbname=' . $db . ';host=' . $hosts[0] . ((isset($hosts[1]) && $hosts[1] != 3306) ? ';port=' . $hosts[1] : ''))
            ->user($username)
            ->pass($password)
            ->tmp($tmp);

        new Import($dump);
    }
}