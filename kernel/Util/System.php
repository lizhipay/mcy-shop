<?php
declare(strict_types=1);


namespace Kernel\Util;

class System
{
    /**
     * @return int|null
     */
    public static function getBitSize(): ?int
    {
        if (PHP_INT_SIZE === 4) {
            return 32;
        } else if (PHP_INT_SIZE === 8) {
            return 64;
        } else {
            return null;
        }
    }


    /**
     * @param int $port
     * @param string $host
     * @return bool
     */
    public static function checkPortAvailable(int $port, string $host = "127.0.0.1"): bool
    {
        $connection = @fsockopen($host, $port);
        if (is_resource($connection)) {
            fclose($connection);
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return int
     */
    public static function getRandPort(): int
    {
        $port = rand(1024, 65535);
        if (self::checkPortAvailable($port)) {
            return $port;
        } else {
            return self::getRandPort();
        }
    }
}