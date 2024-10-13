<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Controller\User\Base;
use App\Model\RepertoryItemMarkupTemplate;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;

class ItemMarkupTemplate extends Base
{
    #[Required("模版ID不能为空", \Kernel\Validator\Required::LOOSE)]
    #[Regex("/^[1-9]\d*$/", "模版ID格式错误")]
    public function id(mixed $id): bool|string
    {
        if ($id === null) {
            return true;
        }

        if (!RepertoryItemMarkupTemplate::query()->where("user_id", $this->getUser()->id)->where("id", $id)->exists()) {
            return '模版不存在';
        }
        return true;
    }
}