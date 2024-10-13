<?php
declare (strict_types=1);

namespace App\Const;

interface Sms
{
    public const PLATFORM_ALI = 0;
    public const PLATFORM_TENCENT = 1;
    public const PLATFORM_DXB = 2;


    public const CAPTCHA_KEY_REGISTER = "REG_PHONE_%s";
    public const CAPTCHA_KEY_FORGET = "FORGET_PHONE_%s";
    public const CAPTCHA_KEY_BIND_NEW = "BIND_NEW_PHONE_%s";

    public const CAPTCHA_TYPE_REGISTER = 0;
    public const CAPTCHA_TYPE_FORGET = 1;
    public const CAPTCHA_TYPE_BIND_NEW = 2;
}