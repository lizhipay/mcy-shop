<?php
declare (strict_types=1);

namespace App\Service\Common;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Sms::class)]
interface Sms
{

    /**
     * 发送短信
     * @param array $config
     * @param string $phone
     * @param string $templateCode
     * @param array $var
     * @return void
     */
    public function send(array $config, string $phone, string $templateCode, array $var = []): void;
}