<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use Kernel\Annotation\Required;

class Submit
{


    #[Required("插件不能为空")]
    public function name(): bool
    {
        return true;
    }


    #[Required("JS不能为空")]
    public function js(): bool
    {
        return true;
    }

}