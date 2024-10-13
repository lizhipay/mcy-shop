<?php
declare (strict_types=1);

namespace Kernel\Validator;

interface Required
{

    //极端的，必须包含一个值
    public const EXTREME = 0;


    //宽松的，如果没有携带参数，则不验证是否为空
    public const LOOSE = 2;
}