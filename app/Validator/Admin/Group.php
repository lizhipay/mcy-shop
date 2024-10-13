<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;
use Kernel\Validator\Required as RequiredAlias;

class Group
{
    #[Required("权限组名称不能为空", RequiredAlias::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("请上传权限组图标", RequiredAlias::LOOSE)]
    public function icon(): bool
    {
        return true;
    }
}