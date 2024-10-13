<?php
declare (strict_types=1);

namespace Kernel\Database\Const;

interface Db
{
    //读未提交
    const ISOLATION_READ_UNCOMMITTED = "READ UNCOMMITTED";
    //读已提交
    const ISOLATION_READ_COMMITTED = "READ COMMITTED";
    //可重复读
    const ISOLATION_REPEATABLE_READ = "REPEATABLE READ";
    //可串行化
    const ISOLATION_SERIALIZABLE = "SERIALIZABLE";
}