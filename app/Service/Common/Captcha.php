<?php
declare (strict_types=1);

namespace App\Service\Common;

use Kernel\Annotation\Bind;

#[Bind(class: \App\Service\Common\Bind\Captcha::class)]
interface Captcha
{

    /**
     * @param string $key
     * @param int $expire
     * @param int $limiter
     * @return string
     */
    public function create(string $key, int $expire, int $limiter = 60): string;


    /**
     * @param string $key
     * @param string $code
     * @return bool
     */
    public function verify(string $key, string $code): bool;


    /**
     * @param string $key
     * @return void
     */
    public function destroy(string $key): void;
}