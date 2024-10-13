<?php
declare (strict_types=1);

namespace App\Const;

interface RepertoryItem
{
    //不支持退款
    public const REFUND_MODE_NOT = 0;

    //有条件退款
    public const REFUND_MODE_CONDITION = 1;

    //无条件退款
    public const REFUND_MODE_UNCONDITIONALLY = 2;
}