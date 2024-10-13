<?php
declare (strict_types=1);

namespace App\Validator\Install;

use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Context\App;
use Kernel\Util\Process;

class Server
{

    #[Required("服务名称不能为空")]
    #[Regex("/^(?=.*[a-zA-Z].*[a-zA-Z])[a-zA-Z-_]+$/", "服务名称必须是2位或以上的英文字符")]
    public function cliName(): bool
    {
        return true;
    }

    #[Required("监听地址不能为空")]
    #[Regex("/^(0\.0\.0\.0|127\.0\.0\.1|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/", "监听地址格式错误")]
    public function cliHost(): bool
    {
        return true;
    }


    #[Required("监听端口不能为空")]
    #[Regex("/^([0-9]{1,5})$/", "监听端口范围：1~65535")]
    public function cliPort(mixed $value): bool|string
    {
        if ($value < 1 || $value > 65535) {
            return "监听端口范围：1~65535";
        }
        return true;
    }

    #[Required("CPU数量不能为空")]
    #[Regex("/^(auto|[1-9]\d*)$/", "CPU数量不正确")]
    public function cliCpu(mixed $value): bool|string
    {
        if ($value == "auto") {
            return true;
        }

        if (App::$cli && $value > (Process::cpuNum() * 2)) {
            return "CPU数量超过阈值";
        }

        return true;
    }
}