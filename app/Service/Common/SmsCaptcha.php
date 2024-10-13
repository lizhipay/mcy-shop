<?php
declare (strict_types=1);

namespace App\Service\Common;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\SmsCaptcha::class)]
interface SmsCaptcha
{
    /**
     * @param string $key
     * @param string $phone
     * @return void
     */
    public function sendCaptcha(string $key, string $phone): void;

    /**
     * @param string $key
     * @param string $phone
     * @param int $code
     * @return bool
     */
    public function checkCaptcha(string $key, string $phone, int $code): bool;


    /**
     * @param string $key
     * @param string $phone
     */
    public function destroyCaptcha(string $key, string $phone): void;
}