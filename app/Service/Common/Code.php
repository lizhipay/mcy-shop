<?php
declare (strict_types=1);

namespace App\Service\Common;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Code::class)]
interface Code
{

    /**
     * 创建验证码
     * @param string $key
     * @param int $expire
     * @return int
     */
    public function create(string $key, int $expire = 60): int;
}