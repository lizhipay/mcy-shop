<?php
declare (strict_types=1);

namespace App\Validator\Install;

use App\Model\User;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Exception\JSONException;
use Kernel\Util\Context;

class Database
{

    #[Required("数据库地址不能为空")]
    #[Regex("/^(localhost|(\d{1,3}\.){3}\d{1,3})(:\d+)?$/", "数据库地址格式错误")]
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