<?php
declare (strict_types=1);

namespace App\Validator\User;

use App\Const\Session;
use App\Controller\User\Base;
use App\Service\Common\Captcha;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Regex;
use Kernel\Annotation\Required;
use Kernel\Util\Str;

class Security extends Base
{

    #[Inject]
    private Captcha $captcha;

    /**
     * @param string $value
     * @return bool|string
     */
    public function email(mixed $value): bool|string
    {
        if (!$value) {
            return "邮箱不能为空";
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return '邮箱格式错误';
        }

        if (\App\Model\User::query()->where("email", $value)->exists()) {
            return '该邮箱已被他人使用';
        }

        return true;
    }


    #[Regex("/^\d{6}$/", "当前邮箱验证码错误")]
    public function currentEmailCode(mixed $value): bool|string
    {
        //如果当前没有绑定邮箱，则不验证
        if (!$this->getUser()->email) {
            return true;
        }

        if (!$value) {
            return '当前邮箱验证码不能为空';
        }

        if (!$this->captcha->verify(sprintf(Session::EMAIL_CODE, "edit_current", $this->getUser()->email), $value)) {
            return "当前邮箱验证码错误";
        }
        return true;
    }

    #[Required("新邮箱的验证码不能为空")]
    #[Regex("/^\d{6}$/", "新邮箱验证码错误")]
    public function newEmailCode(mixed $value): bool|string
    {
        if (!$this->captcha->verify(sprintf(Session::EMAIL_CODE, "edit_new", $this->request->post("email")), $value)) {
            return "新邮箱验证码错误";
        }
        return true;
    }


    #[Required("当前登录密码不能为空")]
    public function currentPassword(mixed $value): bool|string
    {
        if (Str::generatePassword($value, $this->getUser()->salt) != $this->getUser()->password) {
            return "当前登录密码错误";
        }
        return true;
    }

    #[Required("新密码不能为空")]
    #[Regex(Auth::password, "登录密码应为字母、数字、特殊符号(~!@#$%^&*()_.)，两种及以上组合，8-26位字符串")]
    public function newPassword(mixed $value): bool|string
    {
        return true;
    }

    #[Required("确认密码不能为空")]
    public function reNewPassword(mixed $value): bool|string
    {
        if ($value != $this->request->post("new_password")) {
            return "两次密码输入不一致";
        }
        return true;
    }


    #[Required("证件类型不能为空")]
    public function type(mixed $value): bool|string
    {
        if (!in_array($value, [0, 1, 2, 3])) {
            return "证件类型错误";
        }
        return true;
    }

    #[Required("姓名不能为空")]
    public function name(): bool
    {
        return true;
    }

    #[Required("证件号码不能为空")]
    public function idCard(mixed $value): bool|string
    {
        $type = $this->request->post("type");
        switch ($type) {
            case 0:
                if (!preg_match("/^[1-9]\d{5}(18|19|20)?\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}([0-9]|X)$/", $value)) {
                    return "您输入的证件号码不是一个有效的中国居民身份证";
                }
                break;
            case 1:
                if (!preg_match("/^[A-Z]{3}\d{6}\([0-9A]\)$/", $value)) {
                    return "您输入的证件号码不是一个有效的香港永久居民身份证";
                }
                break;
            case 2:
                if (!preg_match("/^[1-7]\d{7}$/", $value)) {
                    return "您输入的证件号码不是一个有效的澳门永久性居民身份证";
                }
                break;
            case 3:
                if (!preg_match("/^[A-Z0-9]{6,9}$/", $value)) {
                    return "您输入的证件号码不是一个有效的护照号码";
                }
                break;
        }
        return true;
    }

}