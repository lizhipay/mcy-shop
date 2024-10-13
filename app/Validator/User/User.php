<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Controller\User\Base;
use App\Model\UserLevel;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class User extends Base
{
    #[Required("会员ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "会员ID格式错误")]
    public function userId(mixed $value): bool|string
    {
        if (!\App\Model\User::query()->where("id", $value)->where("pid", $this->getUser()->id)->exists()) {
            return "会员不存在";
        }
        return true;
    }

    #[Required("等级ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "等级ID格式错误")]
    public function levelId(mixed $value): bool|string
    {
        if (!UserLevel::query()->where("user_id", $this->getUser()->id)->where("id", $value)->exists()) {
            return "等级不存在";
        }
        return true;
    }

}