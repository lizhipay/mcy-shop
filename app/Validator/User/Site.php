<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Controller\User\Base;
use Kernel\Annotation\Name;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Site extends Base
{


    #[Required("类型不能为空")]
    #[Regex("/^[01]$/", "类型错误")]
    public function type(): bool
    {
        return true;
    }


    #[Required("SSL证书(PEM)不能为空")]
    public function pem(): bool
    {
        return true;
    }

    #[Required("SSL域名证书的私钥不能为空")]
    public function key(): bool
    {
        return true;
    }


    #[Required("域名不能为空")]
    public function domain(mixed $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && !preg_match("/^(\*\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/", $value)) {
            return '该域名无效';
        }
        return true;
    }


    #[Name("domain")]
    #[Required("域名不能为空")]
    public function existsDomain(mixed $value): bool|string
    {
        if (!filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && !preg_match("/^(\*\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/", $value)) {
            return '该域名无效';
        }

        if (!\App\Model\Site::where("user_id", $this->getUser()->id)->where("host", $value)->exists()) {
            return '该域名不存在';
        }

        return true;
    }
}