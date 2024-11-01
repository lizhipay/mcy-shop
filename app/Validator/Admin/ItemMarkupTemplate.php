<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class ItemMarkupTemplate
{
    #[Required("模板名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("基数不能为空", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^(0\.\d+|[1-9]\d*(\.\d+)?)$/", "基数必须大于0")]
    public function driftBaseAmount(): bool
    {
        return true;
    }

    #[Required("浮动值不能为空", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^(0\.\d+|[0-9]\d*(\.\d+)?)$/", "浮动值错误")]
    public function driftValue(): bool
    {
        return true;
    }

    #[Required("加价模式选择错误", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[0123]+$/", "加价模式错误")]
    public function driftModel(): bool
    {
        return true;
    }
}