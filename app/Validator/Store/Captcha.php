<?php
declare (strict_types=1);

namespace App\Validator\Store;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Captcha
{
    #[Required("验证码类型不能为空")]
    #[Regex("/^(login|register|reset)$/", "验证码类型错误")]
    public function type(): bool
    {
        return true;
    }
}