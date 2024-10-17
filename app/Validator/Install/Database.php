<?php
declare (strict_types=1);

namespace App\Validator\Install;

use Kernel\Annotation\Required;

class Database
{

    #[Required("数据库地址不能为空")]
    public function dbHost(): bool
    {
        return true;
    }

    #[Required("数据库名称不能为空")]
    public function dbName(): bool
    {
        return true;
    }

    #[Required("数据库用户名不能为空")]
    public function dbUser(): bool
    {
        return true;
    }

    #[Required("数据库密码不能为空")]
    public function dbPass(): bool
    {
        return true;
    }

    #[Required("数据库前缀不能为空")]
    public function dbPrefix(): bool
    {
        return true;
    }
}