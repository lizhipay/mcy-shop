<?php
declare (strict_types=1);

namespace App\Service\Common;


use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Ip::class)]
interface Ip
{

    /**
     * 获取IP归属地
     * @param string $ip
     * @return string
     */
    public function getLocation(string $ip): string;
}