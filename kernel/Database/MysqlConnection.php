<?php
declare (strict_types=1);

namespace Kernel\Database;

use Kernel\Pool\Connection;

class MysqlConnection implements Connection
{

    /**
     * @return ConnectionProxy
     */
    public function createObject(): ConnectionProxy
    {
        return ConnectionProxy::create();
    }
}