<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Model\UserGroup;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class OpenMerchant
{
    #[Required("用户组ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "用户组ID错误")]
    public function groupId(mixed $value): bool|string
    {
        if (!UserGroup::query()->where("id", $value)->exists()) {
            return "用户组不存在";
        }
        return true;
    }
}