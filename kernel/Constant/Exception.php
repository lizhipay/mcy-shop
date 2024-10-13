<?php
declare (strict_types=1);

namespace Kernel\Constant;

interface Exception
{
    /**
     * 找不到页面
     */
    const NOT_FOUND = "404 Not Found";

    /**
     * 请求方法不存在
     */
    const NOT_ALLOW_METHOD = "Method Not Allow";
}