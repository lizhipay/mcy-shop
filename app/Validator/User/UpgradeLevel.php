<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Model\UserLevel;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class UpgradeLevel
{
    #[Required("等级ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "等级ID错误")]
    public function levelId(mixed $value): bool|string
    {
        if (!UserLevel::query()->where("id", $value)->exists()) {
            return "等级不存在";
        }
        return true;
    }
}