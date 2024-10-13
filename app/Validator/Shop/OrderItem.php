<?php
declare (strict_types=1);

namespace App\Validator\Shop;

use App\Model\User;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Util\Context;

class OrderItem
{


    #[Required("物品ID不能为空")]
    #[Regex("/^[1-9]\d*$/", "物品ID错误")]
    public function id(mixed $value): bool|string
    {
        /**
         * @var User $user
         */
        $user = Context::get(User::class);
        $orderItem = \App\Model\OrderItem::find($value);

        if (!$orderItem || !$user || $orderItem->user_id != $user?->id) {
            return '物品不存在';
        }

        return true;
    }
}