<?php
declare (strict_types=1);

namespace App\Validator\Admin;

use App\Controller\Admin\Base;
use App\Validator\User\Auth;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Util\Str;
use Kernel\Util\Verify;

class Personal extends Base
{


    public function currentPassword(mixed $value, array $data): bool|string
    {
        if ($this->request->post("reset_password") == 0) {
            return true;
        }

        if (!$value) {
            return "当前登录密码不能为空";
        }

        if (Str::generatePassword($value, $this->getManage()->salt) != $this->getManage()->password) {
            return "当前登录密码错误";
        }
        return true;
    }

    #[Regex(Auth::password, "登录密码应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串")]
    public function newPassword(mixed $value): bool|string
    {
        if ($this->request->post("reset_password") == 0) {
            return true;
        }

        if (!$value) {
            return "新密码不能为空";
        }

        return true;
    }

    public function reNewPassword(mixed $value): bool|string
    {
        if ($this->request->post("reset_password") == 0) {
            return true;
        }

        if (!$value) {
            return "请再次输入确认密码";
        }

        if ($value != $this->request->post("new_password")) {
            return "两次密码输入不一致";
        }
        return true;
    }


    #[Required("请上传头像")]
    public function avatar(mixed $value): bool|string
    {
        return true;
    }
}