<?php
declare (strict_types=1);

namespace App\Const;

interface RepertoryItemSkuCache
{

    public const TYPE_STOCK = 0;
    public const TYPE_HAS_ENOUGH_STOCK = 1;


    public const ACTION_READ_CACHE = 0;
    public const ACTION_READ_SOURCE = 1;
}