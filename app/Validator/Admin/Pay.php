<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Pay
{
    #[Required("请选择支付通道", \Kernel\Validator\Required::LOOSE)]
    public function code(): bool
    {
        return true;
    }

    #[Required("名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("请选择支付配置", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[1-9]\d*$/", "支付配置错误")]
    public function payConfigId(): bool
    {
        return true;
    }
}