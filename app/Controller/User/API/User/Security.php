<?php
declare (strict_types=1);

namespace App\Controller\User\API\User;

use App\Const\Session;
use App\Controller\User\Base;
use App\Entity\Query\Get;
use App\Entity\Query\Save;
use App\Interceptor\PostDecrypt;
use App\Interceptor\User;
use App\Interceptor\Waf;
use App\Model\UserIdentity;
use App\Model\UserLoginLog;
use App\Service\Common\Captcha;
use App\Service\Common\Config;
use App\Service\Common\Query;
use App\Service\Common\Smtp;
use App\Validator\Common;
use Hyperf\Database\Model\Builder;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Util\Call;
use Kernel\Util\Date;
use Kernel\Util\Str;

#[Interceptor(class: [PostDecrypt::class, Waf::class, User::class], type: Interceptor::API)]
class Security extends Base
{

    #[Inject]
    private Query $query;

    #[Inject]
    private Smtp $smtp;

    #[Inject]
    private Captcha $captcha;


    #[Inject]
    private Config $config;

    /**
     * @return Response
     * @throws JSONException
     * @throws RuntimeException
     */
    public function editGeneral(): Response
    {
        $map = $this->request->post();
        $save = new Save(\App\Model\User::class);

        $save->setMap(map: $map, bypass: ["avatar"]);
        $save->setId($this->getUser()->id);
        $save->disableAddable();

        try {
            $this->query->save($save);
        } catch (\Exception $exception) {
            throw new JSONException("保存失败，错误：" . $exception->getMessage());
        }

        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    public function sendCurrentEmailCode(): Response
    {
        $config = $this->config->getMainConfig("register");
        $code = $this->captcha->create(sprintf(Session::EMAIL_CODE, "edit_current", $this->getUser()->email), 360);
        $to = $this->getUser()->email;

        if (!isset($config['email_bind_state']) || $config['email_bind_state'] != 1) {
            throw new RuntimeException("邮箱绑定未开启");
        }

        //异步发送邮件
        Call::create(function () use ($config, $code, $to) {
            $this->smtp->send($to, "【正在更改邮箱】验证码", str_replace('{$code}', $code, $config['email_template']));
        });
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Security::class, "email"]
    ])]
    public function sendNewEmailCode(): Response
    {
        $config = $this->config->getMainConfig("register");
        $to = $this->request->post("email");
        $code = $this->captcha->create(sprintf(Session::EMAIL_CODE, "edit_new", $to), 360);

        if (!isset($config['email_bind_state']) || $config['email_bind_state'] != 1) {
            throw new RuntimeException("邮箱绑定未开启");
        }

        //异步发送邮件
        Call::create(function () use ($config, $code, $to) {
            $this->smtp->send($to, "【正在绑定邮箱】验证码", str_replace('{$code}', $code, $config['email_template']));
        });
        return $this->json();
    }

    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Security::class, ["email", "currentEmailCode", "newEmailCode"]]
    ])]
    public function bindNewEmail(): Response
    {
        \App\Model\User::query()->where("id", $this->getUser()->id)->update([
            "email" => $this->request->post("email")
        ]);
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Security::class, ["currentPassword", "newPassword", "reNewPassword"]]
    ])]
    public function editPassword(): Response
    {
        \App\Model\User::query()->where("id", $this->getUser()->id)->update([
            "password" => Str::generatePassword($this->request->post("new_password"), $this->getUser()->salt)
        ]);
        return $this->json();
    }


    /**
     * @throws RuntimeException
     */
    #[Validator([
        [\App\Validator\User\Security::class, ["type", "name", "idCard"]]
    ])]
    public function identity(): Response
    {
        if (UserIdentity::query()->where("user_id", $this->getUser()->id)->exists()) {
            throw new RuntimeException("已经提交过实名认证");
        }

        $config = $this->config->getMainConfig("register");
        $userIdentity = new UserIdentity();
        $userIdentity->user_id = $this->getUser()->id;
        $userIdentity->name = $this->request->post("name");
        $userIdentity->id_card = $this->request->post("id_card");
        $userIdentity->type = $this->request->post("type");
        $userIdentity->create_time = Date::current();
        $userIdentity->status = (int)$config['identity_status'];
        $userIdentity->status == 1 && ($userIdentity->review_time = $userIdentity->create_time);
        $userIdentity->save();
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     * @throws \Exception
     */
    public function resubmitIdentity(): Response
    {
        /**
         * @var UserIdentity $identity
         */
        $identity = UserIdentity::query()->where("user_id", $this->getUser()->id)->first();
        if (!$identity) {
            throw new RuntimeException("未提交过实名认证");
        }

        if ($identity->status != 2) {
            throw new RuntimeException("实名认证状态不是审核失败");
        }

        $identity->delete();
        return $this->json();
    }


    /**
     * @return Response
     * @throws RuntimeException
     */
    #[Validator([
        [Common::class, ["page", "limit"]]
    ])]
    public function loginLog(): Response
    {
        $map = $this->request->post();
        $get = new Get(UserLoginLog::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("id", "desc");
        $data = $this->query->get($get, function (Builder $builder) use ($map) {
            return $builder->where("user_id", $this->getUser()->id);
        });
        return $this->json(data: $data);
    }
}