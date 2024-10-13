<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Model\User;
use App\Model\UserLevel;
use Kernel\Annotation\Required;
use Kernel\Util\Context;
use Kernel\Validator\Required as RequiredAlias;

class Level
{
    #[Required("等级名称不能为空", RequiredAlias::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("ID不能为空", RequiredAlias::LOOSE)]
    public function id(mixed $value): bool|string
    {
        if (is_null($value)) {
            return true;
        }

        /**
         * @var User $user
         */
        $user = Context::get(User::class);
        if (!UserLevel::query()->where("user_id", $user->id)->where("id", $value)->exists()) {
            return "等级不存在";
        }

        return true;
    }
}