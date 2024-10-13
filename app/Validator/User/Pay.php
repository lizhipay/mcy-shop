<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Controller\User\Base;
use App\Model\PluginConfig;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class Pay extends Base
{
    #[Required("请选择支付通道", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[a-zA-Z0-9]+$/", "支付通道错误")]
    public function code(): bool
    {
        return true;
    }

    #[Required("名称不能为空", \Kernel\Validator\Required::LOOSE)]
    public function name(): bool
    {
        return true;
    }

    #[Required("请选择支付配置", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[1-9]\d*$/", "支付配置错误")]
    public function payConfigId(mixed $value): bool|string
    {
        if ($value === null) {
            return true;
        }
        /**
         * @var PluginConfig $payConfig
         */
        $payConfig = PluginConfig::query()->find($value);
        if (!$payConfig || $payConfig->user_id !== $this->getUser()->id) {
            return "支付配置不存在";
        }
        return true;
    }

    #[Required("请选择上级支付接口", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[1-9]\d*$/", "上级支付接口选择错误")]
    public function pid(mixed $value): bool|string
    {
        if ($value === null) {
            return true;
        }
        /**
         * @var \App\Model\Pay $pay
         */
        $pay = \App\Model\Pay::query()->find($value);
        if (!$pay || $pay->user_id !== null) {
            return "上级支付接口不存在";
        }
        return true;
    }

    #[Required("id不能为空", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[1-9]\d*$/", "id格式错误")]
    public function id(mixed $value): bool|string
    {
        if ($value === null) {
            return true;
        }

        /**
         * @var \App\Model\Pay $pay
         */
        $pay = \App\Model\Pay::query()->find($value);
        if ($pay->user_id != $this->getUser()->id) {
            return "支付通道不存在";
        }
        return true;
    }
}