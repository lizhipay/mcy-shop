<?php
declare (strict_types=1);

namespace App\Validator\Store;

use Kernel\Annotation\Required;

class Install
{
    #[Required("要安装的插件不能为空")]
    public function key(): bool
    {
        return true;
    }
}